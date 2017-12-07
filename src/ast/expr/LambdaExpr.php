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
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Types\FnType;
use \QuackCompiler\Types\TypeVar;

class LambdaExpr extends Node implements Expr
{
    use Parenthesized;

    public $parameters;
    public $body;
    public $complex;

    public function __construct($parameters, $body, $complex)
    {
        $this->parameters = $parameters;
        $this->body = $body;
        $this->complex = $complex;
    }

    public function format(Parser $parser)
    {
        $source = 'fn ';

        if ($this->complex) {
            $source .= '(';
            $source .= implode(', ', array_map(function ($parameter) use ($parser) {
                return $parameter->format($parser);
            }, $this->parameters));
            $source .= ')';
        } else {
            list ($parameter) = $this->parameters;
            $source .= $parameter->format($parser);
        }

        $source .= ' -> ';
        $source .= $this->body->format($parser);

        return $this->parenthesize($source);
    }

    public function injectScope($parent_scope)
    {
    }

    public function analyze(Scope $scope, Set $non_generic)
    {
        $new_env = new Scope($scope);
        $new_non_generic = clone $non_generic;

        $parameters = [];
        foreach ($this->parameters as $param) {
            $arg_type = new TypeVar();
            $new_env->insert($param->name, Symbol::S_VARIABLE);
            $new_env->setMeta(Meta::M_TYPE, $param->name, $arg_type);
            $new_non_generic->push($arg_type);
            $parameters[$param->name] = $arg_type;
        }

        $construct = function ($acc, $elem) use ($parameters) {
            return new FnType($parameters[$elem->name], $acc);
        };

        $body = $this->body->analyze($new_env, $new_non_generic);
        return array_reduce(array_reverse($this->parameters), $construct, $body);
    }
}
