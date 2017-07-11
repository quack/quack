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
namespace QuackCompiler\Parselets;

use \QuackCompiler\Lexer\Token;

trait Parselet
{
    private $prefix = [];
    private $infix = [];

    protected function register($tag, $parselet)
    {
        if ($parselet instanceof PrefixParselet) {
            $this->prefix[$tag] = $parselet;
        } elseif ($parselet instanceof InfixParselet) {
            $this->infix[$tag] = $parselet;
        }
    }

    public function infixParseletForToken(Token $token)
    {
        $key = $token->getTag();
        return array_key_exists($key, $this->infix)
            ? $this->infix[$key]
            : null;
    }

    public function prefixParseletForToken(Token $token)
    {
        $key = $token->getTag();
        return array_key_exists($key, $this->prefix)
            ? $this->prefix[$key]
            : null;
    }

    private function getPrecedence()
    {
        $parselet = $this->infixParseletForToken($this->reader->lookahead);
        return !is_null($parselet)
            ? $parselet->getPrecedence()
            : 0;
    }
}