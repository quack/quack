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

use \QuackCompiler\Parselets\IncludeParselet;
use \QuackCompiler\Parser\Parser;

class IncludeExpr extends Expr
{
    public $type;
    public $is_once;
    public $file;

    public function __construct($type, $is_once, $file)
    {
        $this->type = $type;
        $this->is_once = $is_once;
        $this->file = $file;
    }

    public function format(Parser $parser)
    {
        $source = IncludeParselet::TYPE_REQUIRE === $this->type
            ? 'require '
            : 'include ';

        if ($this->is_once) {
            $source .= 'once ';
        }

        $source .= $this->file->format($parser);

        if ($this->parenthesize) {
            $source = '(' . $source . ')';
        }

        return $source;
    }
}
