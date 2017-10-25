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
        return $this->name;
    }

    public function bindScope(Scope $scope)
    {
        // TODO
    }

    public function check(TypeNode $other)
    {
        if (!($other instanceof TaggedUnion)) {
            return false;
        }

        // Success when both are references to the same tagged union
        return $other === $this;
    }
}
