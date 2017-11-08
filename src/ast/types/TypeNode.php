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

use \QuackCompiler\Scope\Scope;

abstract class TypeNode
{
    protected $parentheses_level = 0;

    public function getReference()
    {
        return null;
    }

    public function addParentheses()
    {
        $this->parentheses_level++;
    }

    public function removeParentheses()
    {
        $this->parentheses_level--;
    }

    protected function parenthesize($source)
    {
        $level = $this->parentheses_level;
        return str_repeat('(', $level) . $source . str_repeat(')', $level);
    }

    public function isNumber()
    {
        return $this->name === 'Number';
    }

    public function isString()
    {
        return $this->name === 'String';
    }

    public function isRegex()
    {
        return $this->name === 'Regex';
    }

    public function isIterable()
    {
        return $this instanceof MapType || $this instanceof ListType;
    }

    public function simplify()
    {
        return $this;
    }

    public function getKind()
    {
        return '*';
    }

    abstract function check(TypeNode $other);
}
