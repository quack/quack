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
use \QuackCompiler\Types\Type;

class ArrayExpr extends Expr
{
    public $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function format(Parser $parser)
    {
        $source = '{';
        if (sizeof($this->items) > 0) {
            $source .= ' ';
            $source .= implode('; ',
                array_map(function ($item) use ($parser) {
                    return $item->format($parser);
                }, $this->items)
            );
            $source .= ' ';
        }
        $source .= '}';

        return $this->parenthesize($source);
    }

    public function injectScope(&$parent_scope)
    {
        foreach ($this->items as $item) {
            $item->injectScope($parent_scope);
        }
    }

    public function getType()
    {
        $newtype = new Type(NativeQuackType::T_LIST);
        $newtype->subtype = null;
        $type_list = [];

        foreach ($this->items as $item) {
            $type = $item->getType();
            $type_list[] = $type;

            if (null !== $newtype->subtype) {
                if (!$type->isCompatibleWith($newtype->subtype)) {
                    throw new ScopeError([
                        'message' => "Cannot add element of type `{$type}' to `{$newtype}'"
                    ]);
                }
            } else {
                // Infer according to the first type
                $newtype->subtype = $item->getType();
            }
        }

        if (null === $newtype->subtype) {
            $newtype->subtype = new Type(NativeQuackType::T_LAZY);
        } else if ($newtype->subtype->isNumber()) {
            $newtype->subtype->code = max(array_map(function ($type) { return $type->code; }, $type_list));
        }
        // TODO: Implement base-type lookup (with parent reference). Use Hindley-Milner theory

        return $newtype;
    }
}
