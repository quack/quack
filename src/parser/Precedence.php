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
namespace QuackCompiler\Parser;

class Precedence
{
    const ASSIGNMENT         = 1;
    const PIPELINE           = 2;
    const MEMBER_ACCESS      = 3;
    const TERNARY            = 4;
    const COALESCENCE        = 5;
    const RANGE              = 6;
    const LOGICAL_OR         = 7;
    const LOGICAL_XOR        = 8;
    const LOGICAL_AND        = 9;
    const BITWISE_OR         = 10;
    const BITWISE_XOR        = 11;
    const BITWISE_AND_OR_REF = 12;
    const VALUE_COMPARATOR   = 13;
    const SIZE_COMPARATOR    = 14;
    const BITWISE_SHIFT      = 15;
    const ADDITIVE           = 16;
    const MULTIPLICATIVE     = 17;
    const PREFIX             = 18;
    const POSTFIX            = 19;
    const TYPE_CAST          = 20;
    const EXPONENT           = 21;
    const CALL               = 22;
    const ACCESS             = 23;
    // TODO: Review operators precedence when finish the parser
}
