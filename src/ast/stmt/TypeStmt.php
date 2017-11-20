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
namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Ast\Types\NameType;
use \QuackCompiler\Ast\Types\TypeNode;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Scope\Meta;

class TypeStmt extends Stmt
{
    public $name;
    public $value;

    public function __construct($name, TypeNode $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function format(Parser $parser)
    {
        $source = 'type ';
        $source .= $this->name;
        $source .= ' :- ';
        $source .= $this->value;
        $source .= PHP_EOL;
        return $source;
    }

    public function injectScope($parent_scope)
    {
        $this->scope = $parent_scope;
        $flags = Symbol::S_TYPE | Symbol::S_ALIAS;
        $meta = [Meta::M_TYPE => $this->value];

        // Clone signature and meta properties when it is a named reference
        $reference = $this->value->getReference();
        if (null !== $reference) {
            list ($ref_flags, $ref_meta) = $reference;
            // Bind reference flags to type alias
            $flags |= $ref_flags;
            // Bind reference meta table to type alias
            $meta = $ref_meta;
        }

        $this->scope->insert($this->name, $flags);
        foreach ($meta as $key => $value) {
            $this->scope->setMeta($key, $this->name, $value);
        }
    }

    public function runTypeChecker()
    {
        // Pass
    }
}
