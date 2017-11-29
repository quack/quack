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
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Pretty\Parenthesized;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Types\FnType;
use \QuackCompiler\Types\HindleyMilner;
use \QuackCompiler\Types\TypeVar;

class CallExpr extends Node implements Expr
{
    use Parenthesized;

    public $callee;
    public $arguments;

    public function __construct($callee, $arguments)
    {
        $this->callee = $callee;
        $this->arguments = $arguments;
    }

    public function format(Parser $parser)
    {
        $source = $this->callee->format($parser);
        $source .= '(';
        $source .= implode(', ', array_map(function(Expr $arg) use ($parser) {
            return $arg->format($parser);
        }, $this->arguments));
        $source .= ')';

        return $this->parenthesize($source);
    }

    public function injectScope($parent_scope)
    {
        $this->scope = new Scope($parent_scope);
        $this->callee->injectScope($this->scope);

        foreach ($this->arguments as $argument) {
            $argument->injectScope($this->scope);
        }
    }

    public function analyze(Scope $scope, Set $non_generic)
    {
        $argument = $this->arguments[0];

        $fn_type = $this->callee->analyze($scope, $non_generic);
        $arg_type = $argument->analyze($scope, $non_generic);

        $result_type = new TypeVar();
        HindleyMilner::unify(new FnType($arg_type, $result_type), $fn_type);

        return $result_type;
    }
}
