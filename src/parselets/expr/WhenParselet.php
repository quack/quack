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
namespace QuackCompiler\Parselets\Expr;

use \QuackCompiler\Parser\Precedence;
use \QuackCompiler\Parser\Grammar;
use \QuackCompiler\Ast\Expr\WhenExpr;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parselets\PrefixParselet;

class WhenParselet implements PrefixParselet
{
    public function parse($grammar, Token $token)
    {
        $cases = [];
        $default = null;

        do {
            // Only one default operation allowed
            if (null === $default && $grammar->reader->is(Tag::T_ELSE)) {
                $grammar->reader->consume();
                $default = new \stdClass;
                $default->condition = null;
                $default->action = $grammar->_expr();
                $cases[] = $default;
            } else {
                $case = new \stdClass;
                $case->condition = $grammar->_expr();
                $grammar->reader->match('->');
                $case->action = $grammar->_expr();
                $cases[] = $case;
            }

            if (!$grammar->reader->is(Tag::T_END)) {
                $grammar->reader->match(',');
            } else {
                break;
            }
        } while (true);

        $grammar->reader->match(Tag::T_END);

        return new WhenExpr($cases);
    }
}
