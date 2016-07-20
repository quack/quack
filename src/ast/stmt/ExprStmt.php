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
namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class ExprStmt implements Stmt
{
    public $expr_list;

    public function __construct($expr_list)
    {
        $this->expr_list = $expr_list;
    }

    public function format(Parser $parser)
    {
        $source = 'do ';
        $first = true;

        foreach ($this->expr_list as $expr) {
            if (!$first) {
                $source .= $parser->indent();
                $source .= ' , ';
            } else {
                $first = false;
            }

            $source .= $expr->format($parser);
            $source .= PHP_EOL;
        }

        return $source;
    }
}
