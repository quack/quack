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

use \QuackCompiler\Ast\Types\ListType;
use \QuackCompiler\Ast\Types\MapType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Types\TypeError;

class AccessExpr extends Expr
{
    public $left;
    public $index;

    public function __construct($left, $index)
    {
        $this->left = $left;
        $this->index = $index;
    }

    public function format(Parser $parser)
    {
        $source = $this->left->format($parser);
        $source .= ' {';
        $source .= $this->index->format($parser);
        $source .= '}';

        return $this->parenthesize($source);
    }

    public function injectScope($parent_scope)
    {
        $this->left->injectScope($parent_scope);
        $this->index->injectScope($parent_scope);
    }

    public function getType()
    {
        $left_type = $this->left->getType();
        $index_type = $this->index->getType();

        // Access is valid for lists
        if ($left_type instanceof ListType) {
            if (!$index_type->isNumber()) {
                throw new TypeError(Localization::message('TYP040', [$index_type]));
            }

            // Return the subtype of the list as type of access
            return $left_type->type;
        }

        // Access is valid for maps
        if ($left_type instanceof MapType) {
            if (!$index_type->check($left_type->key)) {
                throw new TypeError(Localization::message('TYP050', [$left_type->key, $index_type]));
            }

            // Return the value type of a map
            return $left_type->value;
        }

        // Access is valid for strings
        if ($left_type->isString()) {
            if (!$index_type->isNumber()) {
                throw new TypeError(Localization::message('TYP040', [$index_type]));
            }

            // Return string itself
            return $left_type;
        }

        throw new TypeError(Localization::message('TYP060', [$left_type]));
    }
}
