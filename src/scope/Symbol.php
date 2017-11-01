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

class Symbol
{
    // Base flags
    const S_VARIABLE = 1 << 1;
    const S_TYPE     = 1 << 2;
    const S_LABEL    = 1 << 3;

    // Additional flags
    const S_DATA         = 1 << 4;
    const S_DATA_MEMBER  = 1 << 5;
    const S_DATA_PARAM   = 1 << 6;
    const S_MUTABLE      = 1 << 6;
    const S_PARAMETER    = 1 << 7;
    const S_INITIALIZED  = 1 << 11;
    const S_ALIAS        = 1 << 12;
    const S_GENERIC_VAR  = 1 << 13;
}
