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

use \QuackCompiler\Ast\Types\GenericType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\ScopeError;

class FnSignatureStmt extends Stmt
{
    public $name;
    public $parameters;
    public $type;

    public function __construct($name, $parameters, $type)
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->type = $type;
    }

    public function format(Parser $parser)
    {
        $source = $this->name . '(';
        $source .= implode(', ', array_map(function($param) {
            $parameter = $param->name;

            if (null !== $param->type) {
                $parameter .= ' :: ' . $param->type;
            }

            return $parameter;
        }, $this->parameters));
        $source .= ')';

        if (!is_null($this->type)) {
            $source .= ': ' . $this->type;
        }

        return $source;
    }

    public function injectScope($parent_scope)
    {
        $this->scope = $parent_scope;
        foreach ($this->parameters as $param) {
            if ($parent_scope->hasLocal($param->name)) {
                throw new ScopeError(Localization::message('SCO060', [$param->name, $this->name]));
            }

            // TODO: inject type too?
            $parent_scope->insert($param->name, Symbol::S_INITIALIZED | Symbol::S_MUTABLE | Symbol::S_VARIABLE | Symbol::S_PARAMETER);
        }
    }

    public function getParametersTypes()
    {
        return array_map(function($parameter) {
            return null === $parameter->type
                ? new GenericType()
                : $parameter->type;
        }, $this->parameters);
    }

    public function runTypeChecker()
    {
        // Pass
    }
}
