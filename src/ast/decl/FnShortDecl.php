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
namespace QuackCompiler\Ast\Decl;

use \QuackCompiler\Ast\Decl;
use \QuackCompiler\Ast\Types\FunctionType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Types\TypeError;
use \QuackCompiler\Types\GenericType;

class FnShortDecl implements Decl
{
    public $name;
    public $params;
    public $expr;
    public $return_type;

    public function __construct($name, $params, $expr, $return_type)
    {
        $this->name = $name;
        $this->params = $params;
        $this->expr = $expr;
        $this->return_type = $return_type;
    }

    public function format(Parser $parser)
    {
        $source = 'fn ' . $this->name . '(';
        $source .= implode(', ', array_map(function ($param) use ($parser) {
            return $param->format($parser);
        }, $this->params));
        $source .= ') :- ';
        $source .= $this->expr->format($parser);
        $source .= PHP_EOL;

        return $source;
    }

    private function checkDuplicatedParams()
    {
        $declared = [];
        foreach ($this->params as $param) {
            $name = $param->name;
            if (isset($declared[$name])) {
                throw new ScopeError(Localization::message('SCO060', [$name, $this->name]));
            }

            $declared[$name] = true;
        }
    }

    public function injectScope($outer)
    {
        $outer->insert($this->name, Symbol::S_VARIABLE);
        $this->scope = new Scope($outer);

        $this->checkDuplicatedParams();
        foreach ($this->params as $param) {
            $param->injectScope($this->scope);
        }

        $this->expr->injectScope($this->scope);
    }

    public function runTypeChecker()
    {
        $param_types = array_map(function ($param) {
            return null === $param->type
                ? new GenericType()
                : $param->type;
        }, $this->params);


        // When return type is provided, preset it
        if (null !== $this->return_type) {
            $function_type = new FunctionType($param_types, $this->return_type);
            $this->scope->setMeta(Meta::M_TYPE, $this->name, $function_type);

            $return = $this->expr->getType();
            if (!$this->return_type->check($return)) {
                throw new TypeError(Localization::message('TYP380', [$this->return_type, $return]));
            }
        } else {
            $return = $this->expr->getType();
            $function_type = new FunctionType($param_types, $return);
            $this->scope->setMeta(Meta::M_TYPE, $this->name, $function_type);
        }
    }
}
