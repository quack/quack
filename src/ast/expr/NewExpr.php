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

class NewExpr extends Expr
{
    public $class_name;
    public $ctor_args;

    public function __construct($class_name, $ctor_args)
    {
        $this->class_name = $class_name;
        $this->ctor_args = $ctor_args;
    }

    public function format(Parser $parser)
    {
        $source = '#';
        $source .= implode('.', $this->class_name);

        if (sizeof($this->ctor_args) > 0) {
            $source .= '[ ';
            $source .= implode('; ', array_map(function ($arg) use ($parser) {
                return $arg->format($parser);
            }, $this->ctor_args));
            $source .= ' ]';
        } else {
            $source .= '[]';
        }

        if ($this->parenthesize) {
            $source = '(' . $source . ')';
        }

        return $source;
    }
}
