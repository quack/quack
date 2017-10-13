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
namespace QuackCompiler\Ast\Types;

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Types\TypeError;

class NameType extends TypeNode
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function bindScope(Scope $parent_scope)
    {
        $this->scope = $parent_scope;
    }

    public function simplify()
    {
        $type = $this->scope->getMeta(Meta::M_TYPE, $this->name);
        return $type->simplify();
    }

    public function check(TypeNode $other)
    {
        // Try to find declared type in scope
        $type_flags = $this->scope->lookup($this->name);
        if (null === $type_flags) {
            throw new TypeError(Localization::message('TYP440', [$this->name]));
        }

        $type = $this->scope->getMeta(Meta::M_TYPE, $this->name);
        return $type->check($other);
    }
}
