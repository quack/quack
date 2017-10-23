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

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Scope\Meta;

class Scope
{
    public $table = [];
    public $parent;
    public $meta = [];
    public $child;

    public function __construct(Scope $parent = null)
    {
        $this->parent = $parent;
        // Keep reference to access scope from parent
        if (null !== $this->parent) {
            $this->parent->child = $this;
        }
    }

    public function hasLocal($symbol)
    {
        return array_key_exists($symbol, $this->table);
    }

    public function insert($symbol, $value)
    {
        if ($this->hasLocal($symbol)) {
            throw new ScopeError(Localization::message('SCO130', [$symbol]));
        }
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
        $scope = $this->getSymbolScope($symbol);

        // Initialize meta table when it doesn't exist
        if (!array_key_exists($symbol, $scope->meta)) {
            $scope->meta[$symbol] = [];
        }

        $scope->meta[$symbol][$property] = $value;
    }

    public function setMetaInContext($property, $value)
    {
        // We set the metadata into the scope with no symbols
        $this->meta[$property] = $value;
    }

    public function getMeta($property, $symbol)
    {
        $scope = $this->getSymbolScope($symbol);

        if (null === $scope || !array_key_exists($symbol, $scope->meta)) {
            return null;
        }

        return array_key_exists($property, $scope->meta[$symbol])
            ? $scope->meta[$symbol][$property]
            : null;
    }

    public function getMetaInContext($property)
    {
        if (array_key_exists($property, $this->meta)) {
            return $this->meta[$property];
        }

        return null !== $this->parent
            ? $this->parent->getMetaInContext($property)
            : null;
    }

    public function getSymbolScope($symbol)
    {
        if ($this->hasLocal($symbol)) {
            return $this;
        }

        return null !== $this->parent
            ? $this->parent->getSymbolScope($symbol)
            : null;
    }
}
