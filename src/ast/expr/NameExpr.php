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
namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Ast\Types\FunctionType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\TypeError;

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

    public function injectScope($parent_scope)
    {
        $this->scope = $parent_scope;
        $symbol = $parent_scope->lookup($this->name);

        if (null === $symbol) {
            throw new ScopeError(Localization::message('SCO020', [$this->name]));
        }

        // When we reach here, we can compute that this symbol is being used
        // We store this info in metatables (like Lua) that can be reached
        // later with Quack compile-time reflection function qk_get_meta(prop, symbol)
        // TODO: Assert a variable is initialized in order to use it. We need
        //       a better fork algorithm in order to check for conditional nodes
        $refcount = $parent_scope->getMeta(Meta::M_REF_COUNT, $this->name);
        if (null === $refcount) {
            $parent_scope->setMeta(Meta::M_REF_COUNT, $this->name, 1);
        } else {
            $parent_scope->setMeta(Meta::M_REF_COUNT, $this->name, $refcount + 1);
        }
    }

    public function getType()
    {
        $symbol = $this->scope->lookup($this->name);

        if ($symbol & Symbol::S_VARIABLE || $symbol & Symbol::S_DATA_MEMBER) {
            return $this->scope->getMeta(Meta::M_TYPE, $this->name);
        }

        throw new TypeError(Localization::message('TYP190', [$this->name]));
    }
}

