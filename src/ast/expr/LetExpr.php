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
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Pretty\Parenthesized;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Types\TypeVar;
use \QuackCompiler\Types\Unification;

class LetExpr extends Node implements Expr
{
    use Parenthesized;

    public $name;
    public $value;
    public $recursive;
    public $body;

    public function __construct($name, Expr $value, $recursive, Expr $body)
    {
        $this->name = $name;
        $this->value = $value;
        $this->recursive = $recursive;
        $this->body = $body;
    }

    public function format(Parser $parser)
    {
        $source = 'let ';

        if ($this->recursive) {
            $source .= 'rec ';
        }

        $source .= $this->name;
        $source .= ' :- ';
        $source .= $this->value->format($parser);
        $source .= ' in' . PHP_EOL;
        $source .= $parser->indent() . $this->body->format($parser);

        return $source;
    }

    public function analyze(Scope $scope, Set $non_generic)
    {
        if ($this->recursive) {
            $new_type = new TypeVar();
            $new_env = clone $scope;
            $new_non_generic = clone $non_generic;
            $new_non_generic->push($new_type);
            $new_env->insert($this->name, Symbol::S_VARIABLE);
            $new_env->setMeta(Meta::M_TYPE, $this->name, $new_type);

            $defn_type = $this->value->analyze($new_env, $new_non_generic);
            Unification::unify($new_type, $defn_type);

            return $this->body->analyze($new_env, $non_generic);
        }

        $defn_type = $this->value->analyze($scope, $non_generic);
        $new_env = clone $scope;
        $new_env->insert($this->name, Symbol::S_VARIABLE);
        $new_env->setMeta(Meta::M_TYPE, $this->name, $defn_type);

        return $this->body->analyze($new_env, $non_generic);
    }
}
