<?php
/**
 * Quack Compiler and toolkit
 * Copyright (C) 2015-2017 Quack and CONTRIBUTORS
 *
 * This file is part of Quack.
 *
 * Quack is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Quack is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Quack.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace QuackCompiler\Parser;

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parselets\Expr\BinaryOperatorParselet;
use \QuackCompiler\Parselets\Expr\LiteralParselet;
use \QuackCompiler\Parselets\Expr\NameParselet;
use \QuackCompiler\Parselets\Expr\PostfixOperatorParselet;
use \QuackCompiler\Parselets\Expr\PrefixOperatorParselet;
use \QuackCompiler\Parselets\Expr\TernaryParselet;
use \QuackCompiler\Parselets\Expr\GroupParselet;
use \QuackCompiler\Parselets\Expr\LambdaParselet;
use \QuackCompiler\Parselets\Expr\ListParselet;
use \QuackCompiler\Parselets\Expr\MemberAccessParselet;
use \QuackCompiler\Parselets\Expr\CallParselet;
use \QuackCompiler\Parselets\Expr\AccessParselet;
use \QuackCompiler\Parselets\Expr\RangeParselet;
use \QuackCompiler\Parselets\Expr\PartialFuncParselet;
use \QuackCompiler\Parselets\Expr\WhereParselet;
use \QuackCompiler\Parselets\Expr\MapParselet;
use \QuackCompiler\Parselets\Expr\ObjectParselet;
use \QuackCompiler\Parselets\Expr\BlockParselet;
use \QuackCompiler\Parselets\Expr\TupleParselet;
use \QuackCompiler\Parselets\Expr\MatchParselet;
use \QuackCompiler\Parselets\Parselet;

class ExprParser
{
    use Attachable;
    use Parselet;

    public $reader;

    public function __construct($reader)
    {
        $this->reader = $reader;
        $this->register('&(', new PartialFuncParselet);
        $this->register(Tag::T_INTEGER, new LiteralParselet);
        $this->register(Tag::T_INT_HEX, new LiteralParselet);
        $this->register(Tag::T_INT_OCT, new LiteralParselet);
        $this->register(Tag::T_INT_BIN, new LiteralParselet);
        $this->register(Tag::T_DOUBLE, new LiteralParselet);
        $this->register(Tag::T_DOUBLE_EXP, new LiteralParselet);
        $this->register(Tag::T_STRING, new LiteralParselet);
        $this->register(Tag::T_REGEX, new LiteralParselet);
        $this->register(Tag::T_IDENT, new NameParselet);
        $this->register(Tag::T_TYPENAME, new NameParselet);
        $this->register(Tag::T_THEN, new TernaryParselet);
        $this->register('..', new RangeParselet);
        $this->register('(', new GroupParselet);
        $this->register('(', new CallParselet);
        $this->register('{', new ListParselet);
        $this->register('{', new AccessParselet);
        $this->register('%{', new ObjectParselet);
        $this->register('#{', new MapParselet);
        $this->register('#(', new TupleParselet);
        $this->register('&{', new BlockParselet);
        $this->register('&', new LambdaParselet);
        $this->register('.', new MemberAccessParselet);
        $this->register(Tag::T_ATOM, new LiteralParselet);
        $this->register(Tag::T_WHERE, new WhereParselet);
        $this->register(Tag::T_MATCH, new MatchParselet);

        $this->prefix('+');
        $this->prefix('-');
        $this->prefix('^^');
        $this->prefix('*');
        $this->prefix('~');
        $this->prefix(Tag::T_NOT);

        $this->infixLeft('+', Precedence::ADDITIVE);
        $this->infixLeft('-', Precedence::ADDITIVE);
        $this->infixLeft('*', Precedence::MULTIPLICATIVE);
        $this->infixLeft('/', Precedence::MULTIPLICATIVE);
        $this->infixLeft(Tag::T_MOD, Precedence::MULTIPLICATIVE);
        $this->infixLeft(Tag::T_AND, Precedence::LOGICAL_AND);
        $this->infixLeft(Tag::T_OR, Precedence::LOGICAL_OR);
        $this->infixLeft(Tag::T_XOR, Precedence::LOGICAL_XOR);
        $this->infixLeft('|', Precedence::BITWISE_OR);
        $this->infixLeft('&', Precedence::BITWISE_AND);
        $this->infixLeft('<<', Precedence::BITWISE_SHIFT);
        $this->infixLeft('>>', Precedence::BITWISE_SHIFT);
        $this->infixLeft('=', Precedence::VALUE_COMPARATOR);
        $this->infixLeft('=~', Precedence::VALUE_COMPARATOR);
        $this->infixLeft('<>', Precedence::VALUE_COMPARATOR);
        $this->infixLeft('<=', Precedence::SIZE_COMPARATOR);
        $this->infixLeft('<', Precedence::SIZE_COMPARATOR);
        $this->infixLeft('>=', Precedence::SIZE_COMPARATOR);
        $this->infixLeft('>', Precedence::SIZE_COMPARATOR);
        $this->infixLeft('|', Precedence::PIPELINE);

        $this->infixRight('**', Precedence::EXPONENT);
        $this->infixRight(':-', Precedence::ASSIGNMENT);
    }

    public function _expr($precedence = 0, $opt = false)
    {
        $token = $this->reader->lookahead;
        $prefix = $this->prefixParseletForToken($token);

        if (is_null($prefix)) {
            if (!$opt) {
                $error_params = [
                    'expected' => 'expression',
                    'found'    => $token,
                    'parser'   => $this->reader
                ];

                if ($this->reader->isEOF()) {
                    throw new EOFError($error_params);
                }

                throw new SyntaxError($error_params);
            }

            return null;
        }

        // We consume the token only when ensure it has a parselet, thus,
        // avoiding to rollback in the tape
        $this->reader->consume();
        $left = $prefix->parse($this, $token);

        while ($precedence < $this->getPrecedence()) {
            $token = $this->reader->consumeAndFetch();
            $infix = $this->infixParseletForToken($token);
            $left = $infix->parse($this, $left, $token);
        }

        return $left;
    }

    private function postfix($tag, $precedence)
    {
        $this->register($tag, new PostfixOperatorParselet($precedence));
    }

    private function prefix($tag)
    {
        $this->register($tag, new PrefixOperatorParselet());
    }

    private function infixLeft($tag, $precedence)
    {
        $this->register($tag, new BinaryOperatorParselet($precedence, false));
    }

    private function infixRight($tag, $precedence)
    {
        $this->register($tag, new BinaryOperatorParselet($precedence, true));
    }

    public function _optExpr()
    {
        return $this->_expr(0, true);
    }
}
