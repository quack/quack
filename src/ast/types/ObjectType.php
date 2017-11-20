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
namespace QuackCompiler\Ast\Types;

use \QuackCompiler\Pretty\Types\ObjectTypeRenderer;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\TypeChecker\ObjectTypeChecker;

class ObjectType extends TypeNode
{
    use ObjectTypeChecker;
    use ObjectTypeRenderer;

    public $properties;

    public function __construct($properties)
    {
        $this->properties = $properties;
    }

    public function __toString()
    {
        $source = '%{';
        $source .= implode(', ', array_map(function($name) {
            return "{$name}: {$this->properties[$name]}";
        }, array_keys($this->properties)));
        $source .= '}';

        return $this->parenthesize($source);
    }
}
