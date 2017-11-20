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

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Pretty\Types\OperatorTypeRenderer;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\TypeChecker\OperatorTypeChecker;
use \QuackCompiler\Types\TypeError;

class OperatorType extends TypeNode
{
    use OperatorTypeChecker;
    use OperatorTypeRenderer;

    public $operator;
    public $left;
    public $right;

    public function __construct($left, $operator, $right)
    {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    public function __toString()
    {
        return $this->parenthesize(
            $this->left . " {$this->operator} " . $this->right
        );
    }

    public function simplify()
    {
        $simple_left = $this->left->simplify();
        $simple_right = $this->right->simplify();

        if ($simple_left instanceof ObjectType && $simple_right instanceof ObjectType) {
            // Merge two object types
            $properties = [];
            foreach ($simple_left->properties as $name => $type) {
                $properties[$name] = $type;
            }

            foreach ($simple_right->properties as $name => $type) {
                // If key already exists, ensure the type is compatible
                if (array_key_exists($name, $properties)) {
                    if (!$properties[$name]->check($type)) {
                        throw new TypeError(Localization::message('TYP400',
                            [$this->left, $this->right, $name, $properties[$name], $type]));
                    }
                } else {
                    $properties[$name] = $type;
                }
            }

            return new ObjectType($properties);
        }

        throw new TypeError(Localization::message('TYP390', [$this->left, $this->right]));
    }
}
