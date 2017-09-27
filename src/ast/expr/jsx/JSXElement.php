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
namespace QuackCompiler\Ast\Expr\JSX;

use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Ast\Types\ObjectType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Types\TypeError;

class JSXElement extends Expr
{
    public $name;
    public $attributes;
    public $children;

    public function __construct($name, $attributes, $children = null)
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->children = $children;
    }

    public function format(Parser $parser)
    {
        if (null === $this->children) {
            return $this->parenthesize("<{$this->name} />");
        }

        $parenthesized = $this->parentheses_level > 0;
        $source = '';

        if ($parenthesized) {
            $parser->openScope();
            $source .= PHP_EOL . $parser->indent();
        }

        $source .= "<{$this->name}>" . PHP_EOL;
        $parser->openScope();

        foreach ($this->children as $child) {
            $source .= $parser->indent();

            if ($child instanceof JSXElement) {
                $source .= $child->format($parser);
            } else {
                $source .= "{ {$child->format($parser) } }";
            }

            $source .= PHP_EOL;
        }

        $parser->closeScope();
        $source .= $parser->indent() . "</{$this->name}>";

        if ($parenthesized) {
            $source .= PHP_EOL;
            $parser->closeScope();
        }

        return $this->parenthesize($source);
    }

    public function injectScope(&$parent_scope)
    {
        if (null === $this->children) {
            return;
        }

        foreach ($this->children as $child) {
            $child->injectScope($parent_scope);
        }
    }

    public function getType()
    {
        // TODO: Use lib/jsx.qkt as model
        // TODO: Check what should be the return type of this based on
        // annotations in React.createElement
        if (null === $this->children) {
            return new ObjectType([]);
        }

        foreach ($this->children as $child) {
            $type = $child->getType();
            if (!($child instanceof JSXElement) && !$type->isString()) {
                throw new TypeError(Localization::message('TYP410', [$type]));
            }
        }

        return new ObjectType([]);
    }
}
