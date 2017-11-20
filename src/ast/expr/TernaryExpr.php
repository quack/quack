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

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Types\TypeError;

class TernaryExpr extends Expr
{
    public $condition;
    public $then;
    public $else;

    public function __construct($condition, $then, $else)
    {
        $this->condition = $condition;
        $this->then = $then;
        $this->else = $else;
    }

    public function format(Parser $parser)
    {
        $source = $this->condition->format($parser);
        $source .= ' then ';
        $source .= $this->then->format($parser);
        $source .= ' else ';
        $source .= $this->else->format($parser);

        return $source;
    }

    public function injectScope($parent_scope)
    {
        $this->scope = $parent_scope;
        $this->condition->injectScope($parent_scope);
        $this->then->injectScope($parent_scope);
        $this->else->injectScope($parent_scope);
    }

    public function getType()
    {
        $bool = $this->scope->getPrimitiveType('Bool');
        $condition = $this->condition->getType();
        if (!$bool->check($condition)) {
            throw new TypeError(Localization::message('TYP240', [$condition]));
        }

        $truthy = $this->then->getType();
        $falsy = $this->else->getType();

        if (!$truthy->check($falsy)) {
            throw new TypeError(Localization::message('TYP250', [$truthy, $falsy]));
        }

        return $truthy;
    }
}
