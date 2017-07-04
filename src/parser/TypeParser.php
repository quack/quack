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

use \QuackCompiler\Lexer\Tag;

trait TypeParser
{
    function _type()
    {
        switch ($this->parser->lookahead->getTag())
        {
            case Tag::T_IDENT:
                return $this->_literal();
            case Tag::T_ATOM:
                return $this->_atom();
            case '%':
                return $this->_instance();
            case '{':
                return $this->_list();
            case '#{':
                return $this->_map();
            default:
                throw new SyntaxError([
                    'expected' => 'type signature',
                    'found'    => $this->parser->lookahead,
                    'parser'   => $this->parser
                ]);
        }
    }

    private function _atom()
    {
        $lexeme = $this->parser->resolveScope($this->parser->consumeAndFetch(Tag::T_ATOM)->getPointer());
        return $lexeme;
    }

    private function _literal()
    {
        $name = $this->identifier();
        $types = ['string', 'number', 'boolean', 'regex'];

        if (!in_array($name, $types, true)) {
            // TODO: Throw TypeSignatureError with specific message
            // TODO: Create TypeSignatureError
            throw new \Exception('Unknown type ' . $name);
        }

        // TODO: Give an object representation of the types in the AST
        return $name;
    }

    private function _instance()
    {
        $this->parser->match('%');
        $instance = $this->qualifiedName();
        return '%' . join('.', $instance);
    }

    private function _list()
    {
        $this->parser->match('{');
        $type = $this->_type();
        $this->parser->match('}');
        return [$type];
    }

    private function _map()
    {
        $this->parser->match('#{');
        $key = $this->_type();
        $this->parser->match(':');
        $value = $this->_type();
        $this->parser->match('}');

        return [$key => $value];
    }

    private function _object()
    {
        // TODO
    }

    private function _function()
    {
        // TODO
    }
}