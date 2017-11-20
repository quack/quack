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
namespace QuackCompiler\Parser;

class Precedence
{
    const ASSIGNMENT         = 1;
    const WHERE              = 2;
    const PIPELINE           = 3;
    const MEMBER_ACCESS      = 4;
    const TERNARY            = 5;
    const RANGE              = 7;
    const LOGICAL_OR         = 8;
    const LOGICAL_XOR        = 9;
    const LOGICAL_AND        = 10;
    const BITWISE_OR         = 11;
    const BITWISE_XOR        = 12;
    const BITWISE_AND        = 13;
    const VALUE_COMPARATOR   = 14;
    const SIZE_COMPARATOR    = 15;
    const BITWISE_SHIFT      = 16;
    const ADDITIVE           = 17;
    const MULTIPLICATIVE     = 18;
    const PREFIX             = 19;
    const POSTFIX            = 20;
    const EXPONENT           = 22;
    const CALL               = 23;
    const ACCESS             = 24;
    // TODO: Review operators precedence when finish the parser

    const INTERSECTION_TYPE  = 1;
}
