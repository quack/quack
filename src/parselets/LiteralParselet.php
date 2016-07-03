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

use \QuackCompiler\Ast\Expr\NumberExpr;
use \QuackCompiler\Ast\Expr\StringExpr;
use \QuackCompiler\Ast\Expr\AtomExpr;
use \QuackCompiler\Ast\Expr\NilExpr;
use \QuackCompiler\Ast\Expr\BoolExpr;
use \QuackCompiler\Ast\Expr\RegexExpr;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parser\Grammar;

class LiteralParselet implements IPrefixParselet
{
    public function parse(Grammar $grammar, Token $token)
    {
        $tag = $token->getTag();

        switch ($tag) {
            case Tag::T_ATOM:
                return new AtomExpr($grammar->parser->resolveScope($token->getPointer()));

            case Tag::T_STRING:
                return new StringExpr($grammar->parser->resolveScope($token->getPointer()));

            case Tag::T_DOUBLE:
            case Tag::T_INTEGER:
                return new NumberExpr(
                    $grammar->parser->resolveScope($token->getPointer()),
                    $tag === Tag::T_DOUBLE ? 'double' : 'int'
                );

            case Tag::T_NIL:
                return new NilExpr;

            case Tag::T_TRUE:
            case Tag::T_FALSE:
                return new BoolExpr($tag === Tag::T_TRUE);

            case Tag::T_REGEX:
                return new RegexExpr($grammar->parser->resolveScope($token->getPointer()));
        }
    }
}
