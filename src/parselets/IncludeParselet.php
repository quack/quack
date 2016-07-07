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
namespace QuackCompiler\Parselets;

use \QuackCompiler\Ast\Expr\IncludeExpr;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parser\Grammar;

class IncludeParselet implements IPrefixParselet
{
    const TYPE_REQUIRE = 0x1;
    const TYPE_INCLUDE = 0x2;

    public function parse(Grammar $grammar, Token $token)
    {
        $type = $token->getTag() === Tag::T_REQUIRE
            ? static::TYPE_REQUIRE
            : static::TYPE_INCLUDE;

        $is_once = $grammar->parser->is(Tag::T_ONCE);

        if ($is_once) {
            $grammar->parser->consume();
        }

        $file = $grammar->_expr();

        return new IncludeExpr($type, $is_once, $file);
    }
}
