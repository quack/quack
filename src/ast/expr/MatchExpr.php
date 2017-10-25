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

use \QuackCompiler\Parser\Parser;

class MatchExpr extends Expr
{
    public $expr;
    public $cases;
    public $else;

    public function __construct($expr, $cases, $else)
    {
        $this->expr = $expr;
        $this->cases = $cases;
        $this->else = $else;
    }

    public function format(Parser $parser)
    {
        $source = 'match ';
        $source .= $this->expr->format($parser);
        $source .= ' with';
        $source .= PHP_EOL;
        $parser->openScope();

        $source .= implode(',' . PHP_EOL, array_map(function ($case) use ($parser) {
            $subsource = $parser->indent();
            $subsource .= (string) $case[0];
            $subsource .= ' :- ';
            $subsource .= $case[1]->format($parser);
            return $subsource;
        }, $this->cases));

        if (count($this->cases) > 0 && null !== $this->else) {
            $source .= ',';
        }

        if (null !== $this->else) {
            $source .= $parser->indent();
            $source .= 'else ';
            $source .= $this->else->format($parser);
        }

        $source .= PHP_EOL;
        $parser->closeScope();
        $source .= $parser->indent();
        $source .= 'end';
        return $source;
    }

    public function injectScope($parent_scope)
    {
    }

    public function getType()
    {
        // TODO
        return null;
    }
}
