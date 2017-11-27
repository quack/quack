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
namespace QuackCompiler\TypeChecker;

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Types\TupleType;
use \QuackCompiler\Types\Type;
use \QuackCompiler\Types\TypeError;

trait TupleTypeChecker
{
    public function check(Type $other)
    {
        if (!($other instanceof TupleType)) {
            return false;
        }

        $left_size = count($this->types);
        $right_size = count($other->types);

        if ($left_size !== $right_size) {
            throw new TypeError(Localization::message('TYP420', [$left_size, $right_size]));
        }

        for ($i = 0; $i < $left_size; $i++) {
            $me = $this->types[$i];
            $you = $other->types[$i];

            if (!$me->check($you)) {
                throw new TypeError(Localization::message('TYP430', [$i + 1, $me, $you]));
            }
        }

        return true;
    }
}
