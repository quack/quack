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
namespace QuackCompiler\Types;

use \QuackCompiler\Ast\Types\TypeNode;
use \QuackCompiler\Scope\Scope;

class TaggedUnion extends TypeNode
{
    private $name;
    private $parameters;
    private $values;

    public function __construct($name, $parameters, $values)
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->values = $values;
    }

    public function __toString()
    {
        $source = $this->name;
        if (count($this->parameters) > 0) {
            $source .= '(';
            $source .= implode(', ', $this->parameters);
            $source .= ')';
        }

        return $source;
    }

    public function bindScope(Scope $scope)
    {
        // TODO Bind generic parameters (if still unbound in DataStmt)
        foreach ($this->values as $value) {
            foreach ($value[1] as $type) {
                $type->bindScope($scope);
            }
        }
    }

    public function check(TypeNode $other)
    {
        if (!($other instanceof TaggedUnion)) {
            return false;
        }

        // Success when both are references to the same data
        return $other === $this;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getConstraint($name_to_find)
    {
        foreach ($this->values as $value) {
            list ($name, $types) = $value;
            if ($name_to_find === $name) {
                return $types;
            }
        }

        return null;
    }
}
