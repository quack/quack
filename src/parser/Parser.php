<?php
/**
 * Quack Compiler and toolkit
 * Copyright (C) 2016 Marcelo Camargo <marcelocamargo@linuxmail.org> and
 * CONTRIBUTORS.
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

use \Exception;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Lexer\Tokenizer;
use \QuackCompiler\Parselets\IPrefixParselet;
use \QuackCompiler\Parselets\IInfixParselet;
use \QuackCompiler\Parselets\BinaryOperatorParselet;
use \QuackCompiler\Parselets\LiteralParselet;
use \QuackCompiler\Parselets\NameParselet;
use \QuackCompiler\Parselets\PostfixOperatorParselet;
use \QuackCompiler\Parselets\PrefixOperatorParselet;
use \QuackCompiler\Parselets\TernaryParselet;
use \QuackCompiler\Parselets\GroupParselet;
use \QuackCompiler\Parselets\FunctionParselet;
use \QuackCompiler\Parselets\IncludeParselet;
use \QuackCompiler\Parselets\ArrayParselet;
use \QuackCompiler\Parselets\NewParselet;
use \QuackCompiler\Parselets\MemberAccessParselet;
use \QuackCompiler\Parselets\WhenParselet;
use \QuackCompiler\Parselets\CallParselet;
use \QuackCompiler\Parselets\AccessParselet;
use \QuackCompiler\Parselets\RangeParselet;
use \QuackCompiler\Parselets\PartialFuncParselet;

abstract class Parser
{
    public $input;
    public $lookahead;
    public $scope_level = 0;

    public $prefix_parselets = [];
    public $infix_parselets = [];

    public function __construct(Tokenizer $input)
    {
        $this->registerParselets();
        $this->input = $input;
        $this->consume();
    }

    private function register($tag, $parselet)
    {
        if ($parselet instanceof IPrefixParselet) {
            $this->prefix_parselets[$tag] = $parselet;
        } elseif ($parselet instanceof IInfixParselet) {
            $this->infix_parselets[$tag] = $parselet;
        }
    }

    private function postfix($tag, $precedence)
    {
        $this->register($tag, new PostfixOperatorParselet($precedence));
    }

    private function prefix($tag, $precedence)
    {
        $this->register($tag, new PrefixOperatorParselet($precedence));
    }

    private function infixLeft($tag, $precedence)
    {
        $this->register($tag, new BinaryOperatorParselet($precedence, false));
    }

    private function infixRight($tag, $precedence)
    {
        $this->register($tag, new BinaryOperatorParselet($precedence, true));
    }

    private function registerParselets()
    {
        $this->register('&(', new PartialFuncParselet);
        $this->register(Tag::T_INTEGER, new LiteralParselet);
        $this->register(Tag::T_DOUBLE, new LiteralParselet);
        $this->register(Tag::T_STRING, new LiteralParselet);
        $this->register(Tag::T_REGEX, new LiteralParselet);
        $this->register(Tag::T_IDENT, new NameParselet);
        $this->register(Tag::T_THEN, new TernaryParselet);
        $this->register('..', new RangeParselet);
        $this->register('(', new GroupParselet);
        $this->register('[', new CallParselet);
        $this->register('{', new ArrayParselet);
        $this->register('{', new AccessParselet);
        $this->register(Tag::T_FN, new FunctionParselet);
        $this->register(Tag::T_REQUIRE, new IncludeParselet);
        $this->register(Tag::T_INCLUDE, new IncludeParselet);
        $this->register('#', new NewParselet);
        $this->register('.', new MemberAccessParselet);
        $this->register('?.', new MemberAccessParselet);
        $this->register(Tag::T_TRUE, new LiteralParselet);
        $this->register(Tag::T_FALSE, new LiteralParselet);
        $this->register(Tag::T_NIL, new LiteralParselet);
        $this->register(Tag::T_ATOM, new LiteralParselet);
        $this->register(Tag::T_WHEN, new WhenParselet);

        $this->prefix('+', Precedence::PREFIX);
        $this->prefix('-', Precedence::PREFIX);
        $this->prefix('^^', Precedence::PREFIX);
        $this->prefix('*', Precedence::PREFIX);
        $this->prefix('@', Precedence::PREFIX);
        $this->prefix('~', Precedence::PREFIX);
        $this->prefix(Tag::T_NOT, Precedence::PREFIX);

        $this->postfix('!', Precedence::POSTFIX);

        $this->infixLeft('+', Precedence::ADDITIVE);
        $this->infixLeft('-', Precedence::ADDITIVE);
        $this->infixLeft('++', Precedence::ADDITIVE);
        $this->infixLeft('*', Precedence::MULTIPLICATIVE);
        $this->infixLeft('/', Precedence::MULTIPLICATIVE);
        $this->infixLeft(Tag::T_MOD, Precedence::MULTIPLICATIVE);
        $this->infixLeft(Tag::T_AND, Precedence::LOGICAL_AND);
        $this->infixLeft(Tag::T_OR, Precedence::LOGICAL_OR);
        $this->infixLeft(Tag::T_XOR, Precedence::LOGICAL_XOR);
        $this->infixLeft('|', Precedence::BITWISE_OR);
        $this->infixLeft('&', Precedence::BITWISE_AND_OR_REF);
        $this->infixLeft('^', Precedence::BITWISE_XOR);
        $this->infixLeft('<<', Precedence::BITWISE_SHIFT);
        $this->infixLeft('>>', Precedence::BITWISE_SHIFT);
        $this->infixLeft('=', Precedence::VALUE_COMPARATOR);
        $this->infixLeft('=~', Precedence::VALUE_COMPARATOR);
        $this->infixLeft('<>', Precedence::VALUE_COMPARATOR);
        $this->infixLeft('<=', Precedence::SIZE_COMPARATOR);
        $this->infixLeft('<', Precedence::SIZE_COMPARATOR);
        $this->infixLeft('>=', Precedence::SIZE_COMPARATOR);
        $this->infixLeft('>', Precedence::SIZE_COMPARATOR);
        $this->infixLeft('|>', Precedence::PIPELINE);
        $this->infixLeft('??', Precedence::COALESCENCE);
        $this->infixLeft('?:', Precedence::TERNARY);

        $this->infixRight('**', Precedence::EXPONENT);
        $this->infixRight(':-', Precedence::ASSIGNMENT);
    }

    public function match($tag)
    {
        $hint = null;

        if ($this->lookahead->getTag() === $tag) {
            return $this->consume();
        }

        // When, after an error, the programmer provided an identifier,
        // we'll calculate the levenshtein distance between the expected lexeme
        // and the provided lexeme and give a hint about
        if (Tag::T_IDENT === $this->lookahead->getTag()) {
            $expected_lexeme = $this->input->keywords_hash[$tag];
            $provided_lexeme = $this->resolveScope($this->lookahead->getPointer());

            $distance = levenshtein($expected_lexeme, $provided_lexeme);

            if ($distance <= 2) {
                $hint = "Did you mean \"{$expected_lexeme}\" instead of \"{$provided_lexeme}\"?";
            }
        }

        throw new SyntaxError([
            'expected' => $tag,
            'found'    => $this->lookahead,
            'parser'   => $this,
            'hint'     => $hint
        ]);
    }

    public function opt($tag)
    {
        if ($this->lookahead->getTag() === $tag) {
            $pointer = $this->consume();
            return $pointer === null ? true : $pointer;
        }
        return false;
    }

    public function is($tag)
    {
        return $this->lookahead->getTag() === $tag;
    }

    public function consume()
    {
        $pointer = $this->lookahead === null ?: $this->lookahead->getPointer();
        $this->lookahead = $this->input->nextToken();
        return $pointer;
    }

    public function consumeAndFetch()
    {
        $clone = $this->lookahead;
        $this->lookahead = $this->input->nextToken();
        return $clone;
    }

    public function resolveScope($pointer)
    {
        return $this->input->getSymbolTable()->get($pointer);
    }

    public function position()
    {
        return ["line" => &$this->input->line, "column" => &$this->input->column];
    }

    public function infixParseletForToken(Token $token)
    {
        $key = $token->getTag();
        return array_key_exists($key, $this->infix_parselets)
            ? $this->infix_parselets[$key]
            : null;
    }

    public function prefixParseletForToken(Token $token)
    {
        $key = $token->getTag();
        return array_key_exists($key, $this->prefix_parselets)
            ? $this->prefix_parselets[$key]
            : null;
    }

    public function openScope()
    {
        $this->scope_level++;
    }

    public function closeScope()
    {
        $this->scope_level--;
    }

    public function indent()
    {
        return str_repeat('  ', $this->scope_level);
    }

    public function dedent()
    {
        return str_repeat('  ', $this->scope_level > 0 ? $this->scope_level - 1 : 0);
    }
}
