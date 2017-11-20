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

use \QuackCompiler\Pretty\Types\MapTypeRenderer;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\TypeChecker\MapTypeChecker;

class MapType extends TypeNode
{
    use MapTypeChecker;
    use MapTypeRenderer;

    public $key;
    public $value;

    public function __construct(TypeNode $key, TypeNode $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function __toString()
    {
        return $this->parenthesize(
            '#{' . $this->key . ': ' . $this->value . '}'
        );
    }
}
