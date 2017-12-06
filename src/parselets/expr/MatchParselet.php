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

use \QuackCompiler\Ast\Expr\MatchExpr;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parselets\PrefixParselet;
use \QuackCompiler\Parser\Grammar;

class MatchParselet implements PrefixParselet
{
    public function parse($grammar, Token $token)
    {
        $expr = $grammar->_expr();
        $cases = [];
        $else = null;
        $grammar->reader->match(Tag::T_WITH);

        do {
            if ($grammar->reader->consumeIf(Tag::T_ELSE)) {
                $else = $grammar->_expr();
                break;
            }

            $pattern = $grammar->type_parser->_type();
            $grammar->reader->match(':-');
            $action = $grammar->_expr();
            $cases[] = [$pattern, $action];
        } while ($grammar->reader->is(','));

        $grammar->reader->match(Tag::T_END);
        return new MatchExpr($expr, $cases, $else);
    }
}
