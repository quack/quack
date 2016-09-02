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

    public function __construct($keys, $values)
    {
        $this->keys = $keys;
        $this->values = $values;
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
        $newtype = new Type(NativeQuackType::T_MAP);
        // TODO: Implement contravariance by subtyping of a -> b
        // TODO: ${ 1->${}; 2-> ${ 1 -> 1 } } should throw error

        $size = sizeof($this->keys);
        if ($size > 0) {
            $newtype->props = ['key' => null, 'value' => null];

            for ($i = 0; $i < $size; $i++) {
                $my_key_type = $this->keys[$i]->getType();
                $my_val_type = $this->values[$i]->getType();

                if (null === $newtype->props['key']) {
                    // First time. We don't even need to check value
                    $newtype->props['key'] = $my_key_type;
                    $newtype->props['value'] = $my_val_type;
                } else {
                    if (!$newtype->props['key']->isCompatibleWith($my_key_type)) {
                        throw new ScopeError([
                            'message' => "Key number {$i} of map expected to be `{$newtype->props['key']}'. Got `{$my_key_type}'"
                        ]);
                    }

                    if (!$newtype->props['value']->isCompatibleWith($my_val_type)) {
                        throw new ScopeError([
                            'message' => "Value number {$i} of map expected to be `{$newtype->props['value']}'. Got `{$my_val_type}'"
                        ]);
                    }

                    // When we reach here, we can infer the base
                    $newtype->props['key'] = Type::getBaseType([$newtype->props['key'], $my_key_type]);
                    $newtype->props['value'] = Type::getBaseType([$newtype->props['value'], $my_val_type]);
                }
            }
        } else {
            $newtype->props = [
                'key'   => new Type(NativeQuackType::T_LAZY),
                'value' => new Type(NativeQuackType::T_LAZY)
            ];
        }

        return $newtype;
    }
}
