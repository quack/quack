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

class WhenExpr extends Expr
{
    public $cases;

    public function __construct($cases)
    {
        $this->cases = $cases;
    }

    public function format(Parser $parser)
    {
        $source = 'when';
        $source .= PHP_EOL;

        $parser->openScope();

        for ($i = 0, $l = sizeof($this->cases); $i < $l; $i++) {
            $obj = $this->cases[$i];

            $source .= $parser->indent();
            $source .= '| ';

            if (null !== $obj->condition) {
                $source .= $obj->condition->format($parser);
                $source .= ' -> ';
            } else {
                $source .= 'else ';
            }

            $source .= $obj->action->format($parser);

            if ($i + 1 !== $l) {
                $source .= ';';
                $source .= PHP_EOL;
            }
        }

        $parser->closeScope();

        $source .= PHP_EOL;
        $source .= $parser->indent();
        $source .= 'end';

        return $source;
    }
}
