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
namespace QuackCompiler\TypeChecker;

use \QuackCompiler\Ast\Types\ObjectType;
use \QuackCompiler\Ast\Types\TypeNode;

trait ObjectTypeChecker
{
    public function check(TypeNode $other)
    {
        if (!($other instanceof ObjectType)) {
            return false;
        }

        // Get all properties that are in this object but not in the other
        $different_properties = array_diff_key($this->properties, $other->properties);
        if (count($different_properties) > 0) {
            // If there is something like %{a:1}, %{}, so, we are missing `a'
            return false;
        }

        // When properties match their names, try matching their types
        foreach (array_keys($this->properties) as $property) {
            if (!$this->properties[$property]->check($other->properties[$property])) {
                return false;
            }
        }

        return true;
    }
}
