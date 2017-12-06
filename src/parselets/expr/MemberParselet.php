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

use \QuackCompiler\Ast\Expr\MemberExpr;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parselets\InfixParselet;
use \QuackCompiler\Parser\Precedence;

class MemberParselet implements InfixParselet
{
    public function parse($grammar, $left, Token $token)
    {
        $right = null;
        if ($grammar->reader->consumeIf('&(')) {
            $next = $grammar->reader->lookahead;
            $grammar->reader->consume();
            $name = isset($next->lexeme)
                ? $next->lexeme
                : $next->getTag();
            $grammar->reader->match(')');
            $right = "&($name)";
        } else {
            $right = $grammar->name_parser->_identifier();
        }
        return new MemberExpr($left, $right);
    }

    public function getPrecedence()
    {
        return Precedence::MEMBER_ACCESS;
    }
}
