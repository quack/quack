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
namespace QuackCompiler\Lexer;

use \ReflectionClass;

class Tag
{
    /* Constructions */
    const T_IDENT      = 0x100;
    const T_INT_BIN    = 0x101;
    const T_INT_OCT    = 0x102;
    const T_INT_HEX    = 0x103;
    const T_INTEGER    = 0x104;
    const T_DOUBLE     = 0x105;
    const T_DOUBLE_EXP = 0x106;
    const T_STRING     = 0x107;
    const T_ATOM       = 0x108;
    const T_REGEX      = 0x109;
    const T_TYPENAME   = 0x10A;

    /* Keywords */
    const T_IF       = 0x200;
    const T_WHILE    = 0x201;
    const T_DO       = 0x202;
    const T_LET      = 0x203;
    const T_WHERE    = 0x204;
    const T_FOREACH  = 0x205;
    const T_IN       = 0x206;
    const T_CONTINUE = 0x207;
    const T_BREAK    = 0x208;
    const T_AND      = 0x209;
    const T_OR       = 0x20A;
    const T_XOR      = 0x20B;
    const T_ELIF     = 0x20C;
    const T_ELSE     = 0x20D;
    const T_MOD      = 0x20E;
    const T_NOT      = 0x20F;
    const T_FN       = 0x210;
    const T_THEN     = 0x211;
    const T_BEGIN    = 0x212;
    const T_END      = 0x213;
    const T_UNLESS   = 0x214;
    const T_MUT      = 0x215;
    const T_BY       = 0x216;
    const T_TYPE     = 0x217;
    const T_MATCH    = 0x218;
    const T_WITH     = 0x219;
    const T_DATA     = 0x21A;

    public static function getOperatorLexeme($op)
    {
        switch ($op) {
            case Tag::T_NOT:
                return 'not';
            case Tag::T_AND:
                return 'and';
            case Tag::T_OR:
                return 'or';
            case Tag::T_MOD:
                return 'mod';
            case Tag::T_XOR:
                return 'xor';
            default:
                return $op;
        }
    }

    public static function & getPartialOperators()
    {
        static $op_table = [
            '+',
            '-',
            '*',
            '/',
            '**',
            Tag::T_MOD,
            Tag::T_XOR,
            Tag::T_AND,
            Tag::T_OR,
            '<',
            '>',
            '<=',
            '>=',
            '=',
            '<>',
            '=~',
            '<<',
            '>>',
            '~',
            '|',
            '&',
            '|>',
            '.',
            '++',
        ];

        return $op_table;
    }

    public static function getName($tag)
    {
        $token_name = array_search($tag, (new ReflectionClass(__CLASS__))->getConstants(), true);
        // Yeah, I need to do a strict check here (for the glory of Satan of course)
        return false === $token_name ? $tag : $token_name;
    }
}
