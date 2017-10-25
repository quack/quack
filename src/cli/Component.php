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
namespace QuackCompiler\Cli;

abstract class Component
{
    private $state;

    public function __construct($state)
    {
        $this->state = $state;
    }

    protected function setState($state)
    {
        foreach ($state as $prop => $value) {
            $this->state[$prop] = $value;
        }

        $this->render();
    }

    protected function state() {
        $props = func_get_args();
        $result = [];

        foreach ($props as $prop) {
            $result[] = $this->state[$prop];
        }

        return 1 === count($result)
            ? $result[0]
            : $result;
    }

    abstract public function render();
}
