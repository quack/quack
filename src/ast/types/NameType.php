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
namespace QuackCompiler\Ast\Types;

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Pretty\Types\NameTypeRenderer;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\TypeChecker\NameTypeChecker;
use \QuackCompiler\Types\TypeError;

class NameType extends TypeNode
{
    use NameTypeChecker;
    use NameTypeRenderer;

    public $name;
    public $values;
    public $is_generic;

    public function __construct($name, $values, $is_generic = false)
    {
        $this->name = $name;
        $this->values = $values;
        $this->is_generic = $is_generic;
    }

    public function __toString()
    {
        $source = $this->name;
        if (sizeof($this->values) > 0) {
            $source .= '(';
            $source .= implode(', ', $this->values);
            $source .= ')';
        }

        return $source;
    }

    public function simplify()
    {
        $type = $this->scope->getMeta(Meta::M_TYPE, $this->name);
        // When the reference of the type is the same to this, it is a cyclic reference
        if ($this === $type) {
            throw new TypeError(Localization::message('TYP280', [$this->name]));
        }

        return $type->simplify();
    }

    public function getReference()
    {
        $flags = $this->scope->lookup($this->name);
        $meta = $this->scope->getMetaTable($this->name);
        if (null === $flags) {
            return null;
        }

        return [$flags, $meta];
    }

    public function getKind()
    {
        return implode(' -> ', array_fill(0, count($this->values) + 1, '*'));
    }
}
