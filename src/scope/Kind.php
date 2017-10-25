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
namespace QuackCompiler\Scope;

class Kind
{
    // Base flags
    const K_FUNCTION     = 1 << 0;
    const K_VARIABLE     = 1 << 1;
    const K_TYPE         = 1 << 2;
    const K_UNION        = 1 << 3;
    const K_UNION_MEMBER = 1 << 4;

    // Additional flags
    const K_MUTABLE     = 1 << 6;
    const K_PARAMETER   = 1 << 7;
    const K_LABEL       = 1 << 8;
    const K_SPECIAL     = 1 << 9;
    const K_MEMBER      = 1 << 10;
    const K_INITIALIZED = 1 << 11;
    const K_ALIAS       = 1 << 12;
}
