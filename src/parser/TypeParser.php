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

use \QuackCompiler\Ast\Types\AtomType;
use \QuackCompiler\Ast\Types\GenericType;
use \QuackCompiler\Ast\Types\InstanceType;
use \QuackCompiler\Ast\Types\ListType;
use \QuackCompiler\Ast\Types\LiteralType;
use \QuackCompiler\Ast\Types\MapType;
use \QuackCompiler\Ast\Types\ObjectType;
use \QuackCompiler\Ast\Types\TupleType;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Types\NativeQuackType;

trait TypeParser
{
    function _type()
    {
        $left = null;
        switch ($this->parser->lookahead->getTag())
        {
            case Tag::T_IDENT:
                $left = $this->_literal();
                break;
            case Tag::T_ATOM:
                $left = $this->_atom();
                break;
            case '{':
                $left = $this->_list();
                break;
            case '%':
                $left = $this->_instance();
                break;
            case '%{':
                $left = $this->_object();
                break;
            case '#{':
                $left = $this->_map();
                break;
            case '#(':
                $left = $this->_tuple();
                break;
            case '&':
                $left = $this->_function();
                break;
            default:
                throw new SyntaxError([
                    'expected' => 'type signature',
                    'found'    => $this->parser->lookahead,
                    'parser'   => $this->parser
                ]);
        }

        // TODO: Implement parselets in order to types behave like expressions
        // TODO: Make parselets generic and pluggable, in order to allow
        // plugging new parsers
        if ($this->parser->is('|') || $this->parser->is('&')) {
            $symbol = $this->parser->consumeAndFetch()->getTag();
            $right = $this->_type();
            return [$left, $symbol, $right];
        }

        return $left;
    }

    private function _atom()
    {
        $lexeme = $this->parser->resolveScope($this->parser->consumeAndFetch(Tag::T_ATOM)->getPointer());
        return new AtomType($lexeme);
    }

    private function _literal()
    {
        $name = $this->identifier();
        $types = [
            'string'  => NativeQuackType::T_STR,
            'number'  => NativeQuackType::T_NUMBER,
            'boolean' => NativeQuackType::T_BOOL,
            'regex'   => NativeQuackType::T_REGEX,
            'block'   => NativeQuackType::T_BLOCK,
            'unit'    => NativeQuackType::T_UNIT
        ];

        return array_key_exists($name, $types)
            ? new LiteralType($types[$name])
            : new GenericType($name);
    }

    private function _instance()
    {
        $this->parser->match('%');
        $instance = $this->qualifiedName();

        return new InstanceType($instance);
    }

    private function _list()
    {
        $this->parser->match('{');
        $type = $this->_type();
        $this->parser->match('}');

        return new ListType($type);
    }

    private function _map()
    {
        $this->parser->match('#{');
        $key = $this->_type();
        $this->parser->match(':');
        $value = $this->_type();
        $this->parser->match('}');

        return new MapType($key, $value);
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
                $key = $this->identifier();
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
        $return = 'unit';
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

        return [$parameters, $return];
    }
}
