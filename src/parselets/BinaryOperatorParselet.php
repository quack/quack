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

use \QuackCompiler\Parser\Grammar;
use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Ast\Expr\OperatorExpr;
use \QuackCompiler\Lexer\Token;

class BinaryOperatorParselet implements IInfixParselet
{
    public $precedence;
    public $is_right;

    public function __construct($precedence, $is_right)
    {
        $this->precedence = $precedence;
        $this->is_right = $is_right;
    }

    public function parse(Grammar $parser, Expr $left, Token $token)
    {
        $right = $parser->_expr($this->precedence - ($this->is_right ? 1 : 0));
        return new OperatorExpr($left, $token->getTag(), $right);
    }

    public function getPrecedence()
    {
        return $this->precedence;
    }
}
