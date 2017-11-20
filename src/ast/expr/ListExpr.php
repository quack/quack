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
namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Ast\Types\GenericType;
use \QuackCompiler\Ast\Types\ListType;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Types\TypeError;

class ListExpr extends Expr
{
    public $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function format(Parser $parser)
    {
        $source = '{';
        if (count($this->items) > 0) {
            $source .= implode(', ', array_map(function($item) use ($parser) {
                return $item->format($parser);
            }, $this->items));
        }
        $source .= '}';

        return $this->parenthesize($source);
    }

    public function injectScope($parent_scope)
    {
        foreach ($this->items as $item) {
            $item->injectScope($parent_scope);
        }
    }

    public function getType()
    {
        // Empty array, generic type
        if (0 === count($this->items)) {
            return new ListType(new GenericType());
        }

        // Decidable type
        $subtype = $this->items[0]->getType();
        foreach (array_slice($this->items, 1) as $item) {
            $type = $item->getType();
            if (!$subtype->check($type)) {
                throw new TypeError(Localization::message('TYP020', [$type, $subtype]));
            }
        }

        return new ListType($subtype);
    }
}
