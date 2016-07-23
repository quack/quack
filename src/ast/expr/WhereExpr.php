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

class WhereExpr extends Expr
{
    public $expr;
    public $clauses;

    public function __construct(Expr $expr, $clauses)
    {
        $this->expr = $expr;
        $this->clauses = $clauses;
    }

    public function format(Parser $parser)
    {
        $first = true;
        $size = sizeof($this->clauses);
        $processed = 0;

        $source = $this->expr->format($parser);
        $source .= PHP_EOL;

        $parser->openScope();

        $source .= $parser->indent();
        $source .= 'where ';

        foreach ($this->clauses as $key => $value) {
            $processed++;

            if (!$first) {
                $source .= $parser->indent();
                $source .= '    ; ';
            } else {
                $first = false;
            }

            $source .= $key;
            $source .= ' :- ';
            $source .= $value->format($parser);

            if ($processed < $size) {
                $source .= PHP_EOL;
            }
        }

        $parser->closeScope();

        return $source;
    }
}
