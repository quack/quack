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

use \QuackCompiler\Ast\Types\GenericType;
use \QuackCompiler\Ast\Types\MapType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Types\TypeError;

class MapExpr extends Expr
{
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

    public function getType()
    {
        $size = count($this->keys);

        // Generic type when no initializer provided
        if (0 === $size) {
            return new MapType(new GenericType(), new GenericType());
        }

        $original_key_type = $this->keys[0]->getType();
        $original_value_type = $this->values[0]->getType();

        for ($i = 1; $i < $size; $i++) {
            $key_type = $this->keys[$i]->getType();
            $value_type = $this->values[$i]->getType();

            if (!$original_key_type->check($key_type)) {
                throw new TypeError(Localization::message('TYP070', [$i, $original_key_type, $key_type]));
            }

            if (!$original_value_type->check($value_type)) {
                throw new TypeError(Localization::message('TYP080', [$i, $original_value_type, $value_type]));
            }
        }

        return new MapType($original_key_type, $original_value_type);
    }
}
