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

use \QuackCompiler\Ast\Types\DataType;
use \QuackCompiler\Ast\Types\GenericType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Types\TypeError;

class DataStmt extends Stmt
{
    public $name;
    public $parameters;
    public $values;
    public $type;

    public function __construct($name, $parameters, $values)
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->values = $values;
    }

    public function format(Parser $parser)
    {
        $source = 'data ';
        $source .= $this->name;

        if (sizeof($this->parameters) > 0) {
            $source .= '(';
            $source .= implode(', ', $this->parameters);
            $source .= ')';
        }

        $source .= ' :- ';
        $source .= implode(' or ', array_map(function ($value) use ($parser) {
            return $value->format($parser);
        }, $this->values));

        $source .= PHP_EOL;
        return $source;
    }

    public function injectScope($parent_scope)
    {
        // Bind input parameters
        $this->scope = new Scope($parent_scope);
        foreach ($this->parameters as $parameter) {
            $this->scope->insert($parameter, Symbol::S_DATA_PARAM);
            $this->scope->setMeta(Meta::M_TYPE, $parameter, new GenericType());
        }

        $this->type = new DataType($this->name, $this->parameters);
        $parent_scope->insert($this->name, Symbol::S_TYPE | Symbol::S_DATA);
        $parent_scope->setMeta(Meta::M_TYPE, $this->name, $this->type);

        foreach ($this->values as $value) {
            $value->injectScope($parent_scope);
        }
    }

    public function runTypeChecker()
    {
        foreach ($this->values as $value) {
            $value->runTypeChecker($this->type);
        }
    }
}
