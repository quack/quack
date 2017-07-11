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

use \QuackCompiler\Ast\Types\FunctionType;
use \QuackCompiler\Ast\Types\GenericType;
use \QuackCompiler\Ast\Types\InstanceType;
use \QuackCompiler\Ast\Types\ListType;
use \QuackCompiler\Ast\Types\LiteralType;
use \QuackCompiler\Ast\Types\MapType;
use \QuackCompiler\Ast\Types\ObjectType;
use \QuackCompiler\Ast\Types\TupleType;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parselets\Parselet;
use \QuackCompiler\Parselets\Types\AtomTypeParselet;
use \QuackCompiler\Parselets\Types\BinaryOperatorTypeParselet;
use \QuackCompiler\Parselets\Types\FunctionTypeParselet;
use \QuackCompiler\Parselets\Types\GroupTypeParselet;
use \QuackCompiler\Parselets\Types\InstanceTypeParselet;
use \QuackCompiler\Parselets\Types\ListTypeParselet;
use \QuackCompiler\Parselets\Types\LiteralTypeParselet;
use \QuackCompiler\Parselets\Types\MapTypeParselet;
use \QuackCompiler\Parselets\Types\ObjectTypeParselet;
use \QuackCompiler\Parselets\Types\TupleTypeParselet;
use \QuackCompiler\Types\NativeQuackType;

class TypeParser
{
    use Attachable;
    use Parselet;

    public $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
        $this->register('(', new GroupTypeParselet);
        $this->register(Tag::T_ATOM, new AtomTypeParselet);
        $this->register(Tag::T_IDENT, new LiteralTypeParselet);
        $this->register('{', new ListTypeParselet);
        $this->register('#{', new MapTypeParselet);
        $this->register('#(', new TupleTypeParselet);
        $this->register('%', new InstanceTypeParselet);
        $this->register('%{', new ObjectTypeParselet);
        $this->register('&', new FunctionTypeParselet);
        $this->register('|', new BinaryOperatorTypeParselet(Precedence::UNION_TYPE, false));
        $this->register('&', new BinaryOperatorTypeParselet(Precedence::INTERSECTION_TYPE, false));
    }

    public function _type($precedence = 0)
    {
        $token = $this->parser->lookahead;
        $prefix = $this->prefixParseletForToken($token);

        if (is_null($prefix)) {
            throw new SyntaxError([
                'expected' => 'type signature',
                'found'    => $token,
                'parser'   => $this->parser
            ]);
        }

        $this->parser->consume();
        $left = $prefix->parse($this, $token);

        while ($precedence < $this->getPrecedence()) {
            $token = $this->parser->consumeAndFetch();
            $infix = $this->infixParseletForToken($token);
            $left = $infix->parse($this, $left, $token);
        }

        return $left;
    }
}
