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
namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Ast\Types\FunctionType;
use \QuackCompiler\Ast\Types\GenericType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parselets\Expr\LambdaParselet;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\ScopeError;

class LambdaExpr extends Expr
{
    public $parameters;
    public $kind;
    public $body;
    public $has_brackets;
    private $argument_types;

    public function __construct($parameters, $kind, $body, $has_brackets)
    {
        $this->parameters = $parameters;
        $this->kind = $kind;
        $this->body = $body;
        $this->has_brackets = $has_brackets;
        $this->argument_types = [];
    }

    public function format(Parser $parser)
    {
        $source = '&';

        switch (count($this->parameters)) {
            case 0:
                $source .= '[]';
                break;
            case 1:
                if ($this->has_brackets) {
                    $source .= '[' . $this->parameters[0]->name;
                    if (isset($this->parameters[0]->type)) {
                        $source .= ' :: ' . $this->parameters[0]->type;
                    }
                    $source .= ']';
                } else {
                    $source .= $this->parameters[0]->name;
                }
                break;
            default:
                $source .= '[';
                $source .= implode(', ', array_map(function($param) {
                    $parameter = $param->name;

                    if (null !== $param->type) {
                        $parameter .= " :: {$param->type}";
                    }

                    return $parameter;
                }, $this->parameters));
                $source .= ']';
        }

        $source .= ': ';

        if (LambdaParselet::TYPE_EXPRESSION === $this->kind) {
            $source .= $this->body->format($parser);
        } else {
            $source .= 'begin' . PHP_EOL;
            $source .= $this->body->format($parser);
            $source .= $parser->indent();
            $source .= 'end';
            $source .= PHP_EOL;
        }

        return $this->parenthesize($source);
    }

    public function injectScope($parent_scope)
    {
        $this->scope = new Scope($parent_scope);
        foreach ($this->parameters as $param) {
            if ($this->scope->hasLocal($param->name)) {
                throw new ScopeError(Localization::message('SCO010', [$param->name]));
            }

            $this->scope->insert($param->name, Symbol::S_INITIALIZED | Symbol::S_VARIABLE | Symbol::S_PARAMETER);
            // Use or infer type for parameter and store it
            $param_type = isset($param->type)
                ? $param->type
                : new GenericType();

            $this->argument_types[$param->name] = $param_type;
            $this->scope->setMeta(Meta::M_TYPE, $param->name, $param_type);
        }

        $this->body->injectScope($this->scope);
    }

    public function getType()
    {
        if (LambdaParselet::TYPE_EXPRESSION === $this->kind) {
            return new FunctionType(array_map(function($parameter) {
                return $this->argument_types[$parameter->name];
            }, $this->parameters), $this->body->getType());
        }

        // TODO: Must implement return for blocks
        return null;
    }
}
