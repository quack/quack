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

use \QuackCompiler\Ast\Types\FunctionType;
use \QuackCompiler\Ast\Types\ListType;
use \QuackCompiler\Ast\Types\MapType;
use \QuackCompiler\Ast\Types\ObjectType;
use \QuackCompiler\Ast\Types\TupleType;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parselets\Parselet;
use \QuackCompiler\Parselets\Types\BinaryOperatorTypeParselet;
use \QuackCompiler\Parselets\Types\FunctionTypeParselet;
use \QuackCompiler\Parselets\Types\GroupTypeParselet;
use \QuackCompiler\Parselets\Types\ListTypeParselet;
use \QuackCompiler\Parselets\Types\MapTypeParselet;
use \QuackCompiler\Parselets\Types\NameTypeParselet;
use \QuackCompiler\Parselets\Types\ObjectTypeParselet;
use \QuackCompiler\Parselets\Types\TupleTypeParselet;

class TypeParser
{
    use Attachable;
    use Parselet;

    public $parser;

    public function __construct(Parser $parser)
    {
        $this->reader = $parser;
        $this->register('(', new GroupTypeParselet);
        $this->register(Tag::T_IDENT, new NameTypeParselet(true));
        $this->register(Tag::T_TYPENAME, new NameTypeParselet);
        $this->register('{', new ListTypeParselet);
        $this->register('#{', new MapTypeParselet);
        $this->register('#(', new TupleTypeParselet);
        $this->register('%{', new ObjectTypeParselet);
        $this->register('&', new FunctionTypeParselet);
        $this->register('&', new BinaryOperatorTypeParselet(Precedence::INTERSECTION_TYPE, false));
    }

    public function _type($precedence = 0)
    {
        $token = $this->reader->lookahead;
        $prefix = $this->prefixParseletForToken($token);

        if (is_null($prefix)) {
            $error_params = [
                'expected' => 'type signature',
                'found'    => $token,
                'parser'   => $this->reader
            ];

            if ($this->reader->isEOF()) {
                throw new EOFError($error_params);
            }

            throw new SyntaxError($error_params);
        }

        $this->reader->consume();
        $left = $prefix->parse($this, $token);

        while ($precedence < $this->getPrecedence()) {
            $token = $this->reader->consumeAndFetch();
            $infix = $this->infixParseletForToken($token);
            $left = $infix->parse($this, $left, $token);
        }

        return $left;
    }
}
