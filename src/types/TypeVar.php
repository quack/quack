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

class TypeVar extends Type
{
    private static $next_var_id = 0;
    private static $next_var_name_code = 97;

    public $id;
    public $instance;
    public $name;

    public function __construct()
    {
        $this->id = static::$next_var_id;
        static::$next_var_id++;
        $this->instance = null;
        $this->name = null;
    }

    public function getName()
    {
        if (null === $this->name) {
            $this->name = chr(static::$next_var_name_code);
            static::$next_var_name_code++;
        }

        return $this->name;
    }

    public function __toString()
    {
        if (null !== $this->instance) {
            return (string) $this->instance;
        }

        return $this->getName();
    }

    public function prune()
    {
        if (null !== $this->instance) {
            $this->instance = $this->instance->prune();
            return $this->instance;
        }

        return $this;
    }
}
