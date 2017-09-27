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
namespace QuackCompiler\Parselets\Expr;

use \QuackCompiler\Ast\Expr\JSX\JSXElement;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parselets\PrefixParselet;
use \QuackCompiler\Parser\SyntaxError;

class JSXParselet implements PrefixParselet
{
    private $reader;
    private $name_parser;

    public function parse($grammar, Token $token)
    {
        $this->reader = $grammar->reader;
        $this->name_parser = $grammar->name_parser;
        return $this->JSXElement(true);
    }

    public function JSXElement($partial = false)
    {
        if (!$partial) {
            $this->reader->match('<');
        }

        $name = $this->name_parser->_identifier();

        if ($this->reader->consumeIf('/>')) {
            return new JSXElement($name, [], null);
        }

        $this->reader->match('>');

        $children = [];
        while (!$this->reader->is('</')) {
            $children[] = $this->JSXElement();
        }

        $this->reader->match('</');
        $closing_tag = $this->name_parser->_identifier();

        if ($name !== $closing_tag) {
            throw new SyntaxError([
                'expected' => "</{$name}>",
                'found'    => $this->reader->lookahead,
                'parser'   => $this->reader
            ]);
        }

        $this->reader->match('>');
        return new JSXElement($name, [], $children);
    }
}
