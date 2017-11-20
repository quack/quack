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
namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\ScopeError;

class LabelStmt extends Stmt
{
    public $name;
    public $stmt;

    public function __construct($name, $stmt)
    {
        $this->name = $name;
        $this->stmt = $stmt;
    }

    public function format(Parser $parser)
    {
        $source = '[' . $this->name . ']';
        $source .= PHP_EOL;
        $source .= $parser->indent();
        $source .= $this->stmt->format($parser);
        return $source;
    }

    public function injectScope($parent_scope)
    {
        $this->scope = new Scope($parent_scope);
        // Pre-inject label symbol
        $this->scope->insert($this->name, Symbol::S_LABEL);
        $this->stmt->injectScope($this->scope);
    }

    public function runTypeChecker()
    {
        $this->stmt->runTypeChecker();
    }
}
