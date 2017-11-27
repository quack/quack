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
namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Ast\Expr;
use \QuackCompiler\Ast\Node;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Pretty\Parenthesized;

class PartialFuncExpr extends Node implements Expr
{
    use Parenthesized;

    public $operator;
    public $right;

    public function __construct($operator, $right = null)
    {
        $this->operator = $operator;
        $this->right = $right;
    }

    public function format(Parser $parser)
    {
        $source = '&(';
        $source .= Tag::getOperatorLexeme($this->operator);

        if (null !== $this->right) {
            $source .= ' ';
            $source .= $this->right->format($parser);
        }

        $source .= ')';

        return $this->parenthesize($source);
    }

    public function injectScope($parent_scope)
    {
        $this->right->injectScope($parent_scope);
    }
}
