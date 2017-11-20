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
namespace QuackCompiler\Pretty;

class CliColorizer implements Colorizer
{
    public function bold($value)
    {
        return sprintf('%c[1m%s%c[21m', 0x1B, $value, 0x1B);
    }

    public function red($value)
    {
        return sprintf('%c[31m%s%c[0m', 0x1B, $value, 0x1B);
    }

    public function green($value)
    {
        return sprintf('%c[32m%s%c[0m', 0x1B, $value, 0x1B);
    }

    public function yellow($value)
    {
        return sprintf('%c[33m%s%c[0m', 0x1B, $value, 0x1B);
    }

    public function blue($value)
    {
        return sprintf('%c[34m%s%c[0m', 0x1B, $value, 0x1B);
    }

    public function magenta($value)
    {
        return sprintf('%c[35m%s%c[0m', 0x1B, $value, 0x1B);
    }

    public function cyan($value)
    {
        return sprintf('%c[36m%s%c[0m', 0x1B, $value, 0x1B);
    }
}
