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
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Pretty\Parenthesized;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Types\MapType;
use \QuackCompiler\Types\TypeError;
use \QuackCompiler\Types\TypeVar;
use \QuackCompiler\Types\Unification;

class MapExpr extends Node implements Expr
{
    use Parenthesized;

    public $keys;
    public $values;

    public function __construct($keys, $values)
    {
        $this->keys = $keys;
        $this->values = $values;
    }

    public function format(Parser $parser)
    {
        $source = '#{';
        $keys = $this->keys;
        $values = $this->values;

        if (count($this->keys) > 0) {
            // Iterate based on index
            $source .= implode(', ', array_map(function($index) use ($keys, $values, $parser) {
                $subsource = $keys[$index]->format($parser);
                $subsource .= ': ';
                $subsource .= $values[$index]->format($parser);

                return $subsource;
            }, range(0, count($keys) - 1)));
        }

        $source .= '}';

        return $this->parenthesize($source);
    }

    public function injectScope($parent_scope)
    {
        foreach ($this->keys as $key) {
            $key->injectScope($parent_scope);
        }

        foreach ($this->values as $value) {
            $value->injectScope($parent_scope);
        }
    }

    public function analyze(Scope $scope, Set $non_generic)
    {
        $key_type = new TypeVar();
        $value_type = new TypeVar();

        $size = count($this->keys);
        for ($i = 0; $i < $size; $i++) {
            $inferred_key_type = $this->keys[$i]->analyze($scope, $non_generic);
            $inferred_value_type = $this->values[$i]->analyze($scope, $non_generic);

            try {
                Unification::unify($key_type, $inferred_key_type);
            } catch (TypeError $error) {
                throw new TypeError(Localization::message('TYP070', [$i, $key_type, $inferred_key_type]));
            }

            try {
                Unification::unify($value_type, $inferred_value_type);
            } catch (TypeError $error) {
                throw new TypeError(Localization::message('TYP080', [$i, $value_type, $inferred_value_type]));
            }
        }

        return new MapType($key_type, $value_type);
    }
}
