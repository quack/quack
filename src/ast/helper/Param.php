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
namespace QuackCompiler\Ast\Helper;

use \QuackCompiler\Parser\Parser;

class Param
{
    public $name;
    public $by_reference;
    public $ellipsis;

    public function __construct($name, $by_reference, $ellipsis)
    {
        $this->name = $name;
        $this->by_reference = $by_reference;
        $this->ellipsis = $ellipsis;
    }

    public function format(Parser $parser)
    {
        $string_builder = [];
        if ($this->ellipsis) {
            $string_builder[] = '... ';
        }

        if ($this->by_reference) {
            $string_builder[] = '*';
        }

        $string_builder[] = $this->name;

        return implode($string_builder);
    }
}
