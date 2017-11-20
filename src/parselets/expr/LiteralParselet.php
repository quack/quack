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
namespace QuackCompiler\Parselets\Expr;

use \QuackCompiler\Ast\Expr\NumberExpr;
use \QuackCompiler\Ast\Expr\StringExpr;
use \QuackCompiler\Ast\Expr\AtomExpr;
use \QuackCompiler\Ast\Expr\RegexExpr;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parselets\PrefixParselet;
use \QuackCompiler\Parser\Grammar;

class LiteralParselet implements PrefixParselet
{
    public function parse($grammar, Token $token)
    {
        $tag = $token->getTag();
        $content = $token->getContent();

        switch ($tag) {
            case Tag::T_ATOM:
                return new AtomExpr($content);

            case Tag::T_STRING:
                return new StringExpr($content, $token->metadata['delimiter']);

            case Tag::T_DOUBLE:
            case Tag::T_INTEGER:
                return new NumberExpr($content, $tag === Tag::T_DOUBLE ? 'double' : 'int');

            case Tag::T_INT_HEX:
                return new NumberExpr($content, 'int', 'hexadec');

            case Tag::T_INT_OCT:
                return new NumberExpr($content, 'int', 'octal');

            case Tag::T_INT_BIN:
                return new NumberExpr($content, 'int', 'binary');

            case Tag::T_DOUBLE_EXP:
                return new NumberExpr($content, 'double', 'scientific');

            case Tag::T_REGEX:
                return new RegexExpr($content);
        }
    }
}
