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
namespace QuackCompiler\Types;

use \QuackCompiler\Ds\Set;
use \QuackCompiler\Types\Constraints\RecordConstraint;

class Unification
{
    private function isGeneric($variable, Set $non_generic)
    {
        return !OccursCheck::occursIn($variable, $non_generic);
    }

    private static function fuse(RecordType $left, RecordType $right)
    {
        $fields = [];

        $left_names = array_keys($left->types);
        $right_names = array_keys($right->types);

        $common_fields = array_intersect($left_names, $right_names);
        $exclusive_left_fields = array_diff($left_names, $right_names);
        $exclusive_right_fields = array_diff($right_names, $left_names);

        // Exclusive fields can only be set
        foreach ($exclusive_left_fields as $field) {
            $fields[$field] = $left->types[$field];
        }

        foreach ($exclusive_right_fields as $field) {
            $fields[$field] = $right->types[$field];
        }

        // Common fields are then unified
        foreach ($common_fields as $field) {
            $result = new TypeVar();
            static::unify($left->types[$field], $result);
            static::unify($right->types[$field], $result);
            $fields[$field] = $result;
        }

        // Sort by keys
        ksort($fields);

        // So the origin changes to fuse records
        $left->origin->types = $fields;
        $right->origin->types = $fields;
    }

    private static function decompose(RecordType $actual, RecordConstraint $constraint)
    {
        $missing = array_diff(array_keys($constraint->types), array_keys($actual->types));
        if (count($missing) > 0) {
            $property = $missing[0];
            throw new TypeError('Missing property ' . $property);
        }

        static::fuse($actual, $constraint);
    }

    public function fresh(Type $type, Set $non_generic)
    {
        $mappings = [];
        $freshrec = function (Type $type) use ($non_generic, &$mappings, &$freshrec) {
            $pruned = $type->prune();
            if ($pruned instanceof TypeVar) {
                if (static::isGeneric($pruned, $non_generic)) {
                    if (!isset($mappings[$pruned->id])) {
                        $mappings[$pruned->id] = new TypeVar();
                    }

                    return $mappings[$pruned->id];
                } else {
                    return $pruned;
                }
            } elseif ($pruned instanceof RecordConstraint) {
                return new RecordConstraint(array_map($freshrec, $pruned->types));
            } elseif ($pruned instanceof RecordType) {
                // Preserve record names and reference old expression
                $record = new RecordType(array_map($freshrec, $pruned->types));
                $record->origin = $pruned;
                return $record;
            } elseif ($pruned instanceof FnType) {
                list ($from, $to) = $pruned->types;
                return new FnType($from, $to);
            } elseif ($pruned instanceof TypeOperator) {
                $class = get_class($pruned);
                return new $class($pruned->getName(), array_map($freshrec, $pruned->types));
            }
        };

        return $freshrec($type);
    }

    public function unify(Type $t1, Type $t2)
    {
        $left = $t1->prune();
        $right = $t2->prune();

        if ($left instanceof TypeVar && $left !== $right) {
            if (OccursCheck::occursInType($left, $right)) {
                throw new TypeError('Recursive unification');
            }

            $left->instance = $right;
        }

        elseif ($left instanceof TypeOperator && $right instanceof TypeVar) {
            static::unify($right, $left);
        }

        elseif ($left instanceof RecordConstraint && $right instanceof RecordType) {
            static::unify($right, $left);
        }

        elseif ($left instanceof RecordType && $right instanceof RecordConstraint) {
            static::decompose($left, $right);
        }

        elseif ($left instanceof RecordType && $right instanceof RecordType) {
            static::fuse($left, $right);
        }

        elseif ($left instanceof TypeOperator && $right instanceof TypeOperator) {
            if ($left->name !== $right->name || count($left->types) !== count($right->types)) {
                throw new TypeError('Type mismatch: ' . $left . ' != ' . $right);
            }

            for ($i = 0; $i < count($left->types); $i++) {
                static::unify($left->types[$i], $right->types[$i]);
            }
        }
    }
}
