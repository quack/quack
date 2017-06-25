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
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\ScopeError;

class NewExpr extends Expr
{
    public $class_name;
    public $initializer;

    public function __construct($class_name, $initializer)
    {
        $this->class_name = $class_name;
        $this->initializer = $initializer;
    }

    public function format(Parser $parser)
    {
        $source = '@' . implode('.', $this->class_name);

        $source .= null !== $this->initializer
            ? (' ' . $this->initializer->format($parser))
            : PHP_EOL;

        return $this->parenthesize($source);
    }

    public function injectScope(&$parent_scope)
    {
        // TODO: Implement member check after traversal
        $name = implode('.', $this->class_name);
        $class = $parent_scope->lookup($name);

        // When symbol doesn't exist
        if (null === $class) {
            throw new ScopeError([
                'message' => "Undefined shape `{$name}'"
            ]);
        }

        // When symbol is not a shape
        if (~$class & Kind::K_SHAPE) {
            throw new ScopeError([
                'message' => "Cannot instantiate `{$name}'. Not a shape"
            ]);
        }

        // TODO: Inject scope on initializer (if provided)
    }
}
