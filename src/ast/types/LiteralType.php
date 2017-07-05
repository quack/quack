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

class LiteralType
{
    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function __toString()
    {
        switch ($this->code)
        {
            case NativeQuackType::T_STR:
                return 'string';
            case NativeQuackType::T_NUMBER:
                return 'number';
            case NativeQuackType::T_BOOL:
                return 'boolean';
            case NativeQuackType::T_REGEX:
                return 'regex';
            case NativeQuackType::T_BLOCK:
                return 'block';
            case NativeQuackType::T_UNIT:
                return 'unit';
            default:
                return 'unknown';
        }
    }
}