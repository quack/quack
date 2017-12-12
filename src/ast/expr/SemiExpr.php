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
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Types\TypeVar;
use \QuackCompiler\Types\Unification;

class SemiExpr extends Node implements Expr
{
    use Parenthesized;

    public $sequence;

    public function __construct($sequence)
    {
        $this->sequence = $sequence;
    }

    public function format(Parser $parser)
    {
        $format = function ($expr) use ($parser) {
            return $expr->format($parser);
        };
        $source = implode(';' . PHP_EOL . $parser->indent(), array_map($format, $this->sequence));

        return $this->parenthesize($source);
    }

    public function injectScope($outer)
    {
        // Deprecated
    }

    public function analyze(Scope $scope, Set $non_generic)
    {
        $type = null;
        foreach ($this->sequence as $expr) {
            $type = $expr->analyze($scope, $non_generic);
        }

        return $type;
    }
}
