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

use \QuackCompiler\Ast\Expr\SemiExpr;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parselets\InfixParselet;
use \QuackCompiler\Parser\Precedence;

class SemiParselet implements InfixParselet
{
    public function parse($grammar, $left, Token $token)
    {
        $sequence = [$left];
        do {
            $sequence[] = $grammar->_expr();
        } while ($grammar->reader->consumeIf(';'));

        return new SemiExpr($sequence);
    }

    public function getPrecedence()
    {
        return Precedence::SEMI;
    }
}
