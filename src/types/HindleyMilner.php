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

class HindleyMilner
{
    public static function occursInType($variable, Type $type)
    {
        $pruned_expr = $type->prune();
        if ($pruned_expr === $variable) {
            return true;
        } else if ($pruned_expr instanceof TypeOperator) {
            return static::occursIn($variable, $pruned_expr->types);
        }

        return false;
    }

    public static function occursIn($variable, Set $types)
    {
        return $types->some(function ($subtype) use ($type) {
            return static::occursInType($variable, $subtype);
        });
    }

    public function isGeneric($variable, Set $non_generic)
    {
        return !static::occursIn($variable, $non_generic);
    }
}
