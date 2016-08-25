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

class Type
{
    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function __toString()
    {
        if (null === $this->code) {
            return 'unknown';
        }

        var_dump($this->code);

        switch ($this->code) {
            case NativeQuackType::T_STR:
                return 'string';
            case NativeQuackType::T_INT:
                return 'integer';
            case NativeQuackType::T_DOUBLE:
                return 'double';
            case NativeQuackType::T_BOOL:
                return 'boolean';
            case NativeQuackType::T_ATOM:
                return 'atom';
            case NativeQuackType::T_REGEX:
                return 'regex';
            default:
                return 'unknown';
        }
    }

    public function isString()
    {
        return NativeQuackType::T_STR === $this->code;
    }

    public function isInteger()
    {
        return NativeQuackType::T_INT === $this->code;
    }

    public function isDouble()
    {
        return NativeQuackType::T_DOUBLE === $this->code;
    }

    public function isBoolean()
    {
        return NativeQuackType::T_BOOL === $this->code;
    }

    public function isAtom()
    {
        return NativeQuackType::T_ATOM === $this->code;
    }

    public function isRegex()
    {
        return NativeQuackType::T_REGEX === $this->code;
    }

    public function isNumber()
    {
        return $this->isInteger() || $this->isDouble();
    }
}
