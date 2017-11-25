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
namespace QuackCompiler\Ast\Helpers;

use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Types\GenericType;

class Param
{
    public $name;
    public $type;

    public function __construct($name, $type = null)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function format(Parser $parser)
    {
        $source = $this->name;
        if (null !== $this->type) {
            $source .= ' :: ';
            $source .= $this->type;
        }

        return $source;
    }

    public function injectScope($outer)
    {
        $outer->insert($this->name, Symbol::S_VARIABLE);
        $type = null === $this->type
            ? new GenericType()
            : $this->type;
        $outer->setMeta(Meta::M_TYPE, $this->name, $type);
    }
}
