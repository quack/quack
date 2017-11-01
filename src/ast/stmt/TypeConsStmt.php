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

use \QuackCompiler\Ast\Types\FunctionType;
use \QuackCompiler\Ast\Types\NameType;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Types\Data;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\Symbol;

class TypeConsStmt
{
    public $name;
    public $parameters;
    public $data;

    public function __construct($name, $parameters)
    {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    public function bindData(Data $data)
    {
        $this->data = $data;
    }

    public function format(Parser $parser)
    {
        $source = $this->name;

        if (count($this->parameters) > 0) {
            $source .= '(';
            $source .= implode(', ', $this->parameters);
            $source .= ')';
        }

        return $source;
    }

    private function getData()
    {
        if (count($this->parameters) === 0) {
            // All constraints have been satisfied
            return $this->data;
        }

        return new FunctionType($this->parameters, $this->data, $this->data->parameters);
    }

    public function injectScope($parent_scope, $data_scope)
    {
        $parent_scope->insert($this->name, Symbol::S_DATA_MEMBER);
        $parent_scope->setMeta(Meta::M_TYPE, $this->name, $this->getData());
        foreach ($this->parameters as $parameter) {
            $parameter->bindScope($data_scope);
        }
    }
}
