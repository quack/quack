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

use \QuackCompiler\Ast\Expr;
use \QuackCompiler\Ast\Node;
use \QuackCompiler\Ds\Set;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parselets\Expr\LambdaParselet;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Pretty\Parenthesized;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\FnType;
use \QuackCompiler\Types\TypeVar;

class LambdaExpr extends Node implements Expr
{
    use Parenthesized;

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
    }

    public function analyze(Scope $scope, Set $non_generic)
    {
        $arg_type = new TypeVar();
        $new_env = clone $scope;
        $param = $this->parameters[0];

        $new_env->insert($param->name, Symbol::S_VARIABLE);
        $new_env->setMeta(Meta::M_TYPE, $param->name, $arg_type);

        $new_non_generic = clone $non_generic;
        $new_non_generic->push($arg_type);

        $result_type = $this->body->analyze($new_env, $new_non_generic);

        return new FnType($arg_type, $result_type);
    }
}
