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
namespace QuackCompiler\Ast;

use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Scope;

class Body
{
    public $body;

    public function __construct($body)
    {
        $this->body = $body;
    }

    public function format(Parser $parser)
    {
        $result = '';
        $parser->openScope();

        foreach ($this->body as $stmt) {
            $result .= $parser->indent() . $stmt->format($parser);
        }

        $parser->closeScope();
        return $result;
    }

    public function injectScope($outer)
    {
        $this->scope = $outer;
        foreach ($this->body as $stmt) {
            $stmt->injectScope($this->scope);
        }
    }

    public function runTypeChecker()
    {
        foreach ($this->body as $stmt) {
            $stmt->runTypeChecker();
        }
    }
}
