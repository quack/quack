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
namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\NativeQuackType;

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

    public function injectScope(&$parent_scope) {
        $this->left->injectScope($parent_scope);
        $this->index->injectScope($parent_scope);
    }

    public function getType()
    {
        $left_type = $this->left->getType();
        $index_type = $this->index->getType();

        if (NativeQuackType::T_LIST === $left_type->code) {
            // Expected numeric, integer index
            if (!$index_type->isInteger()) {
                throw new ScopeError([
                    'message' => "Expected index of array to be an integer. Got `{$index_type}'"
                ]);
            }

            return clone $left_type->subtype;
        }

        if ($left_type->isMap()) {
            if (!$index_type->isExactlySameAs($left_type->subtype['key'])) {
                throw new ScopeError([
                    'message' => "Expect index of map to be a `{$left_type->subtype['key']}'. Got `{$index_type}'"
                ]);
            }

            return $left_type->subtype['value'];
        }

        if ($left_type->isString()) {
            return clone $left_type;
        }

        throw new ScopeError([
            'message' => "Trying to access by index an element of type `$left_type' that is not accessible"
        ]);
    }
}
