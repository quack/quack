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

use \QuackCompiler\Ast\Expr\PartialFuncExpr;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parselets\PrefixParselet;
use \QuackCompiler\Parser\Grammar;

class PartialFuncParselet implements PrefixParselet
{
    public function parse($grammar, Token $token)
    {
        $op_table = &Tag::getPartialOperators();
        $next_op = $grammar->reader->lookahead->getTag();
        $right = null;

        if (in_array($next_op, $op_table, true)) {
            $grammar->reader->match($next_op);

            // Faster than _optExpr
            if (!$grammar->reader->is(')')) {
                $right = $grammar->_expr();
            }

            $grammar->reader->match(')');
        } else {
            $grammar->reader->match('operator');
        }

        return new PartialFuncExpr($next_op, $right);
    }
}
