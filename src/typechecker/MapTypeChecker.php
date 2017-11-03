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

use \QuackCompiler\Ast\Types\MapType;
use \QuackCompiler\Ast\Types\TypeNode;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Types\TypeError;

trait MapTypeChecker
{
    public function check(TypeNode $other)
    {
        if (!($other instanceof MapType)) {
            return false;
        }

        $match_keys = $this->key->check($other->key);
        $match_values = $this->value->check($other->value);

        if (!$match_keys || !$match_values) {
            $message = Localization::message('TYP350', [$this, $other]);

            if (!$match_keys) {
                $message .= '     > ' . Localization::message('TYP340',
                    ['key', $this->key, $other->key]);
            }

            if (!$match_values) {
                $message .= PHP_EOL . '     > ' . Localization::message('TYP340',
                    ['value', $this->value, $other->value]);
            }

            throw new TypeError($message);
        }

        return true;
    }
}
