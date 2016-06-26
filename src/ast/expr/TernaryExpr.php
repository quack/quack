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

use \QuackCompiler\Parser\Parser;

class TernaryExpr implements Expr
{
    public $condition;
    public $then;
    public $else;

    public function __construct($condition, $then, $else)
    {
        $this->condition = $condition;
        $this->then = $then;
        $this->else = $else;
    }

    public function format(Parser $parser)
    {
        $string_builder = ['('];
        $string_builder[] = $this->condition->format($parser);
        $string_builder[] = ' ? ';
        $string_builder[] = $this->then->format($parser);
        $string_builder[] = ' : ';
        $string_builder[] = $this->else->format($parser);
        $string_builder[] = ')';
        return implode($string_builder);
    }
}
