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

use \QuackCompiler\Ast\Expr;
use \QuackCompiler\Ast\Node;
use \QuackCompiler\Ds\Set;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Pretty\Parenthesized;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Types\TypeError;
use \QuackCompiler\Types\Unification;

class NameExpr extends Node implements Expr
{
    use Parenthesized;

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
    }

    public function analyze(Scope $scope, $non_generic)
    {
        $symbol = $scope->lookup($this->name);

        if (null === $symbol) {
            throw new ScopeError(Localization::message('SCO020', [$this->name]));
        }

        $refcount = $scope->getMeta(Meta::M_REF_COUNT, $this->name);
        if (null === $refcount) {
            $scope->setMeta(Meta::M_REF_COUNT, $this->name, 1);
        } else {
            $scope->setMeta(Meta::M_REF_COUNT, $this->name, $refcount + 1);
        }

        if ($symbol & Symbol::S_VARIABLE) {
            $type = $scope->getMeta(Meta::M_TYPE, $this->name);

            if (null === $type) {
                throw new TypeError(Localization::message('TYP270', [$this->name]));
            }

            return Unification::fresh($type, $non_generic);
        }

        throw new TypeError(Localization::message('TYP190', [$this->name]));
    }
}

