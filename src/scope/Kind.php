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
namespace QuackCompiler\Scope;

class Kind
{
    // Base flags
    const K_FUNCTION    = 0x1;
    const K_VARIABLE    = 0x2;
    const K_BLUEPRINT   = 0x4;
    const K_SHAPE       = 0x8;
    const K_CLASS       = 0x10;
    const K_ENUM        = 0x20;

    // Additional flags
    const K_MUTABLE     = 0x40;
    const K_PARAMETER   = 0x80;
    const K_LABEL       = 0xFF;
    const K_VIRTUAL     = 0x200;
    const K_EXPORTED    = 0x400;
    const K_SPECIAL     = 0x800;
    const K_MEMBER      = 0x1000;
    const K_INITIALIZED = 0x2000;
    const K_PUB         = 0x4000;
}
