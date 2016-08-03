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
namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parser\Parser;

class OperatorExpr extends Expr
{
    public $left;
    public $operator;
    public $right;

    public function __construct(Expr $left, $operator, $right)
    {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    private function isMemberAccess()
    {
        return '.' === $this->operator || '?.' === $this->operator;
    }

    public function format(Parser $parser)
    {
        $blanks = $this->isMemberAccess() ? '' : ' ';

        $source = $this->left->format($parser);
        $source .= $blanks;
        $source .= Tag::getOperatorLexeme($this->operator);
        $source .= $blanks;
        $source .= $this->isMemberAccess() ? $this->right : $this->right->format($parser);

        return $this->parenthesize($source);
    }

    public function injectScope(&$parent_scope)
    {
        $this->left->injectScope($parent_scope);

        if (!$this->isMemberAccess()) {
            $this->right->injectScope($parent_scope);
        }
    }
}
