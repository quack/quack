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

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parser\Parser;

class OpenStmt implements Stmt
{
    public $module;
    public $alias;
    public $type;
    public $subprops;

    public function __construct($module, $alias = null, $type = null, $subprops = null)
    {
        $this->module = $module;
        $this->alias = $alias;
        $this->type = $type;
        $this->subprops = $subprops;
    }

    public function format(Parser $parser)
    {
        $index = 0;
        $source = 'open ';

        if (null !== $this->type) {
            switch ($this->type->getTag()) {
                case Tag::T_CONST:
                    $source .= 'const ';
                    break;
                case Tag::T_FN:
                    $source .= 'fn ';
                    break;
            }
        }

        if (2 == sizeof($this->module)) {
            $source .= '.';
            $index = 1;
        }

        $source .= implode('.', $this->module[$index]);

        if (null !== $this->alias) {
            $source .= ' as ';
            $source .= $this->alias;
        }

        if (null !== $this->subprops) {
            $source .= ' { ';
            $source .= implode('; ', $this->subprops);
            $source .= ' }';
        }

        $source .= PHP_EOL;

        return $source;
    }
}
