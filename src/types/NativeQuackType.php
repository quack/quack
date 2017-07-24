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

class NativeQuackType
{
    const T_STR      = 0;
    const T_NUMBER   = 2;
    const T_BOOL     = 3;
    const T_OBJ      = 4;
    const T_MAP      = 5;
    const T_LIST     = 6;
    const T_ATOM     = 9;
    const T_REGEX    = 10;
    const T_LAZY     = 11;
    const T_BLOCK    = 12;
    const T_ENUM     = 13;
    const T_UNIT     = 14;
    const T_BYTE     = 15;
}
