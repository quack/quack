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

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\NativeQuackType;
use \QuackCompiler\Types\Type;

class ObjectExpr extends Expr
{
    public $keys;
    public $values;

    public function __construct($keys, $values)
    {
        $this->keys = $keys;
        $this->values = $values;
    }

    public function format(Parser $parser)
    {
        $source = '%{';
        $keys = &$this->keys;
        $values = &$this->values;

        if (sizeof($this->keys) > 0) {
            $source .= PHP_EOL;

            $parser->openScope();

            // Iterate based on index
            $source .= implode(',' . PHP_EOL,
                array_map(function ($index) use (&$keys, &$values, $parser) {
                    $subsource = $parser->indent();
                    $subsource .= $keys[$index];
                    $subsource .= ': ';
                    $subsource .= $values[$index]->format($parser);

                    return $subsource;
                }, range(0, sizeof($keys) - 1))
            );

            $parser->closeScope();

            $source .= PHP_EOL;
            $source .= $parser->indent();
        }

        $source .= '}';

        return $this->parenthesize($source);
    }

    public function injectScope(&$parent_scope)
    {
        $defined = [];
        $index = 0;
        while ($index < sizeof($this->keys)) {
            $key = $this->keys[$index];
            $value = $this->values[$index];

            if (array_key_exists($key, $defined)) {
                throw new ScopeError(Localization::message('SCO050', [$key]));
            }

            $value->injectScope($parent_scope);
            $defined[$key] = true;
            $index++;
        }
    }

    public function getType()
    {
        $newtype = new Type(NativeQuackType::T_OBJ);
        $newtype->props = [];

        for ($i = 0, $size_t = sizeof($this->keys); $i < $size_t; $i++) {
            $newtype->props[$this->keys[$i]] = $this->values[$i]->getType();
        }

        return $newtype;
    }
}
