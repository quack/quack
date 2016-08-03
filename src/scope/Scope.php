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
namespace QuackCompiler\Scope;

class Scope
{
    public $table = [];
    public $parent;
    public $meta = [];

    public function hasLocal($symbol)
    {
        return array_key_exists($symbol, $this->table);
    }

    public function insert($symbol, $value)
    {
        $this->table[$symbol] = $value;
    }

    public function lookup($symbol)
    {
        if ($this->hasLocal($symbol)) {
            return $this->table[$symbol];
        }

        return null !== $this->parent
            ? $this->parent->lookup($symbol)
            : null;
    }

    public function setMeta($property, $symbol, $value)
    {
        // The first thing we need is to locate the scope to inject
        // the metadata
        $scope = &$this->getSymbolScope($symbol);

        // Initialize meta table when it doesn't exist
        if (!array_key_exists($symbol, $scope->meta)) {
            $scope->meta[$symbol] = [];
        }

        $scope->meta[$symbol][$property] = $value;
    }

    public function getMeta($property, $symbol)
    {
        $scope = &$this->getSymbolScope($symbol);

        if (!array_key_exists($symbol, $scope->meta)) {
            return null;
        }

        return array_key_exists($property, $scope->meta[$symbol])
            ? $scope->meta[$symbol][$property]
            : null;
    }

    public function & getSymbolScope($symbol)
    {
        if ($this->hasLocal($symbol)) {
            return $this;
        }

        return null !== $this->parent
            ? $this->parent->getSymbolScope($symbol)
            : null;
    }
}
