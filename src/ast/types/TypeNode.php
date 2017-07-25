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
namespace QuackCompiler\Ast\Types;

use \QuackCompiler\Types\NativeQuackType;

abstract class TypeNode
{
    protected $parentheses_level = 0;

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
        return $this instanceof LiteralType
            && NativeQuackType::T_NUMBER === $this->code;
    }

    public function isString()
    {
        return $this instanceof LiteralType
            && NativeQuackType::T_STR === $this->code;
    }

    public function isBoolean()
    {
        return $this instanceof LiteralType
            && NativeQuackType::T_BOOL === $this->code;
    }
}
