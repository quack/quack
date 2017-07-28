<?php
/**
 * Quack Compiler and toolkit
 * Copyright (C) 2016 Marcelo Camargo <marcelocamargo@linuxmail.org> and
 * CONTRIBUTORS.
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

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Types\NativeQuackType;
use \QuackCompiler\Types\Type;
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
        $keys = &$this->keys;
        $values = &$this->values;

        if (sizeof($this->keys) > 0) {
            $source .= ' ';
            // Iterate based on index
            $source .= implode(', ', array_map(function($index) use (&$keys, &$values, $parser) {
                $subsource = $keys[$index]->format($parser);
                $subsource .= ': ';
                $subsource .= $values[$index]->format($parser);

                return $subsource;
            }, range(0, sizeof($keys) - 1)));
            $source .= ' ';
        }

        $source .= '}';

        return $this->parenthesize($source);
    }

    public function injectScope(&$parent_scope)
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
        $size = sizeof($this->keys);
        $newtype = new Type(NativeQuackType::T_MAP);

        if (0 === $size) {
            $newtype->subtype = [
                'key'   => new Type(NativeQuackType::T_LAZY),
                'value' => new Type(NativeQuackType::T_LAZY)
            ];
            return $newtype;
        }

        $newtype->subtype = [
            'key'   => $this->keys[0]->getType(),
            'value' => $this->values[0]->getType()
        ];

        for ($i = 1; $i < $size; $i++) {
            $key_type = $this->keys[$i]->getType();
            $val_type = $this->values[$i]->getType();

            if (!$key_type->isExactlySameAs($newtype->subtype['key'])) {
                throw new TypeError(Localization::message('TYP070', [$i, $newtype->subtype['key'], $key_type]));
            }

            if (!$val_type->isExactlySameAs($newtype->subtype['value'])) {
                throw new TypeError(Localization::message('TYP080', [$i, $newtype->subtype['value'], $val_type]));
            }
        }

        return $newtype;
    }
}
