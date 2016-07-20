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

use \QuackCompiler\Lexer\Tag;

class TokenChecker
{
    private $parser;

    public function __construct(TokenReader $parser)
    {
        $this->parser = $parser;
    }

    public function startsInnerStmt()
    {
        $possible_inner_stmts = [
            Tag::T_BLUEPRINT,
            Tag::T_STRUCT,
            Tag::T_FN,
            Tag::T_ENUM
        ];

        return in_array($this->parser->lookahead->getTag(), $possible_inner_stmts, true)
            || $this->startsStmt();
    }

    public function startsStmt()
    {
        static $possible_stmts = [
            Tag::T_IF,
            Tag::T_LET,
            Tag::T_CONST,
            Tag::T_WHILE,
            Tag::T_DO,
            Tag::T_FOR,
            Tag::T_FOREACH,
            Tag::T_SWITCH,
            Tag::T_TRY,
            Tag::T_BREAK,
            Tag::T_CONTINUE,
            Tag::T_GOTO,
            Tag::T_GLOBAL,
            Tag::T_RAISE,
            Tag::T_BEGIN,
            '^',
            ':-'
        ];

        $next_tag = $this->parser->lookahead->getTag();
        return in_array($next_tag, $possible_stmts, true);
    }

    public function isEoF()
    {
        return $this->parser->lookahead->getTag() === 0;
    }
}
