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
    const T_IDENT = 253;
    const T_INT_BIN = 255;
    const T_INT_OCT = 256;
    const T_INT_HEX = 257;
    const T_INTEGER = 258;
    const T_DOUBLE = 400;
    const T_DOUBLE_EXP = 401;
    const T_STRING = 600;
    const T_ATOM = 601;
    const T_REGEX = 1001;
    const T_TYPENAME = 1002;

    /* Keywords */
    const T_TRUE = 260;
    const T_FALSE = 261;
    const T_IF = 262;
    const T_WHILE = 264;
    const T_DO = 265;
    const T_LET = 273;
    const T_WHERE = 280;
    const T_FOREACH = 281;
    const T_IN = 284;
    const T_AS = 504;
    const T_CONTINUE = 508;
    const T_BREAK = 510;
    const T_AND = 511;
    const T_OR = 512;
    const T_XOR = 513;
    const T_ELIF = 520;
    const T_ELSE = 521;
    const T_MOD = 292;
    const T_NOT = 293;
    const T_FN = 294;
    const T_THEN = 300;
    const T_BEGIN = 301;
    const T_END = 302;
    const T_UNLESS = 307;
    const T_MUT = 308;
    const T_BY = 309;
    const T_TYPE = 310;
    const T_MATCH = 311;
    const T_WITH = 312;
    const T_UNION = 313;

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
            '??',
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
