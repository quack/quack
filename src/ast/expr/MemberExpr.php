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
use \QuackCompiler\Types\RecordType;
use \QuackCompiler\Types\TypeVar;
use \QuackCompiler\Types\Unification;

class MemberExpr extends Node implements Expr
{
    public $object;
    public $property;

    public function __construct($object, $property)
    {
        $this->object = $object;
        $this->property = $property;
    }

    public function format(Parser $parser)
    {
        $source = $this->object->format($parser);
        $source .= '.';
        $source .= $this->property;

        return $source;
    }

    public function injectScope($outer)
    {
        // Pass
    }

    public function analyze(Scope $scope, Set $non_generic)
    {
        $object_type = $this->object->analyze($scope, $non_generic);
        $result_type = new TypeVar();

        Unification::unify(new RecordType([$this->property => $result_type]), $object_type);

        return $result_type;
    }
}
