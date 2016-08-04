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
namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\ScopeError;

class NameExpr extends Expr
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function format(Parser $parser)
    {
        $source = $this->name;
        return $this->parenthesize($source);
    }

    public function injectScope(&$parent_scope)
    {
        // TODO: Check symbol kind in order to provide better messages
        $symbol = $parent_scope->lookup($this->name);

        if (null === $symbol) {
            throw new ScopeError([
                'message' => "Use of undefined variable `{$this->name}'"
            ]);
        }

        // When we reach here, we can compute that this symbol is being used
        // We store this info in metatables (like Lua) that can be reached
        // later with Quack compile-time reflection function qk_get_meta(prop, symbol)
        // TODO: Assert a variable is initialized in order to use it. We need
        //       a better fork algorithm in order order to check for conditional nodes
        $refcount = &$parent_scope->getMeta('refcount', $this->name);
        if (null === $refcount) {
            $parent_scope->setMeta('refcount', $this->name, 1);
        } else {
            $parent_scope->setMeta('refcount', $this->name, $refcount + 1);
        }
    }
}
