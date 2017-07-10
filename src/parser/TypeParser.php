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
use \QuackCompiler\Parselets\Types\GroupTypeParselet;
use \QuackCompiler\Parselets\Types\InstanceTypeParselet;
use \QuackCompiler\Parselets\Types\ListTypeParselet;
use \QuackCompiler\Parselets\Types\LiteralTypeParselet;
use \QuackCompiler\Parselets\Types\MapTypeParselet;
use \QuackCompiler\Types\NativeQuackType;

class TypeParser
{
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
        $this->register('%', new InstanceTypeParselet);
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
        /*
        switch ($this->parser->lookahead->getTag())
        {
            case '%{':
                $left = $this->_object();
            case '#(':
                $left = $this->_tuple();
            case '&':
                $left = $this->_function();
        }*/
    }

    private function getPrecedence()
    {
        $parselet = $this->infixParseletForToken($this->parser->lookahead);
        return !is_null($parselet)
            ? $parselet->getPrecedence()
            : 0;
    }

    private function _tuple()
    {
        $types = [];
        $this->parser->match('#(');
        if (!$this->parser->consumeIf(')')) {
            do {
                $types[] = $this->_type();
            } while ($this->parser->consumeIf(','));

            $this->parser->match(')');
        }

        return new TupleType(...$types);
    }

    private function _object()
    {
        $this->parser->match('%{');
        $properties = [];

        if (!$this->parser->is('}')) {
            do {
                $key = $this->_identifier();
                $this->parser->match(':');
                $properties[$key] = $this->_type();
            } while ($this->parser->consumeIf(','));
        }
        $this->parser->match('}');

        return new ObjectType($properties);
    }

    private function _function()
    {
        $parameters = [];
        $return = new LiteralType(NativeQuackType::T_UNIT);
        $this->parser->match('&');

        if ($this->parser->is(Tag::T_IDENT)) {
            $parameters[] = $this->_type();
        } else {
            $this->parser->match('[');
            if (!$this->parser->consumeIf(']')) {
                do {
                    $parameters[] = $this->_type();
                } while ($this->parser->consumeIf(','));

                $this->parser->match(']');
            }
        }

        if ($this->parser->consumeIf('->')) {
            $return = $this->_type();
        }

        return new FunctionType($parameters, $return);
    }

    public function _identifier()
    {
        return $this->parser->resolveScope($this->parser->match(Tag::T_IDENT));
    }

    public function _qualifiedName()
    {
        $names = [];
        do {
            $names[] = $this->_identifier();
        } while ($this->parser->consumeIf('.'));

        return $names;
    }
}
