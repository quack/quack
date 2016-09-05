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

use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\NativeQuackType;
use \QuackCompiler\Types\Type;

class MapExpr extends Expr
{
    public $keys;
    public $values;
    private $memoized_type;

    public function __construct($keys, $values)
    {
        $this->keys = $keys;
        $this->values = $values;
        $this->memoized_type = null;
    }

    public function format(Parser $parser)
    {
        $source = '${';
        $keys = &$this->keys;
        $values = &$this->values;

        if (sizeof($this->keys) > 0) {
            $source .= ' ';
            // Iterate based on index
            $source .= implode('; ',
                array_map(function ($index) use (&$keys, &$values, $parser) {
                    $subsource = $keys[$index]->format($parser);
                    $subsource .= ' -> ';
                    $subsource .= $values[$index]->format($parser);

                    return $subsource;
                }, range(0, sizeof($keys) - 1))
            );
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
        if (null !== $this->memoized_type) {
            return $this->memoized_type;
        }

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

            if (!$key_type->isCompatibleWith($newtype->subtype['key'])) {
                throw new ScopeError(['message' => "Key number {$i} of map expected to be `{$newtype->subtype['key']}'. Got `{$key_type}'"]);
            }

            if (!$val_type->isCompatibleWith($newtype->subtype['value'])) {
                 throw new ScopeError(['message' => "Value number {$i} of map expected to be `{$newtype->subtype['value']}'. Got `{$val_type}'"]);
            }
        }

        // TODO: Apply Liskov substitution principle for subtypes. I'm stucked with it for now
        $this->memoized_type = &$newtype;
        return $newtype;
    }
}
