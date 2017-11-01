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

use \QuackCompiler\Pretty\Types\LiteralTypeRenderer;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Types\NativeQuackType;

class LiteralType extends TypeNode
{
    use LiteralTypeRenderer;

    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function __toString()
    {
        $map = [
            NativeQuackType::T_STR    => 'string',
            NativeQuackType::T_NUMBER => 'number',
            NativeQuackType::T_REGEX  => 'regex',
            NativeQuackType::T_BLOCK  => 'block',
            NativeQuackType::T_BYTE   => 'byte',
            NativeQuackType::T_ATOM   => 'atom'
        ];

        return $map[$this->code];
    }

    public function bindScope(Scope $parent_scope)
    {
        // Pass
    }

    public function check(TypeNode $other)
    {
        if (!($other instanceof LiteralType)) {
            // Fallback for atom check
            return $other instanceof AtomType && NativeQuackType::T_ATOM === $this->code;
        }

        return $this->code === $other->code;
    }
}
