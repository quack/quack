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

use \QuackCompiler\Parser\Precedence;
use \QuackCompiler\Parser\Grammar;
use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Ast\Expr\RangeExpr;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Lexer\Tag;

class RangeParselet implements IInfixParselet
{
    public function parse(Grammar $grammar, Expr $from, Token $token)
    {
        $to = $grammar->_expr();
        $by = null;

        if ($grammar->parser->is(Tag::T_BY)) {
            $grammar->parser->consume();
            $by = $grammar->_expr();
        }

        return new RangeExpr($from, $to, $by);
    }

    public function getPrecedence()
    {
        return Precedence::RANGE;
    }
}
