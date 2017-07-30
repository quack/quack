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
namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\ScopeError;

class ImplStmt extends Stmt
{
    public $type;
    public $class_or_shape;

    public $class_for;
    public $body;

    public function __construct($type, $class_or_shape, $class_for, $body)
    {
        $this->type = $type;
        $this->class_or_shape = $class_or_shape;
        $this->class_for = $class_for;
        $this->body = $body;
    }

    private function formatQualifiedName($name)
    {
        return implode('.', $name);
    }

    public function format(Parser $parser)
    {
        $source = 'impl ';
        $source .= $this->formatQualifiedName($this->class_or_shape);

        if (Tag::T_CLASS === $this->type) {
            $source .= ' for ';
            $source .= $this->formatQualifiedName($this->class_for);
        }

        $source .= PHP_EOL;
        $parser->openScope();
        $source .= $this->body->format($parser);
        $parser->closeScope();
        $source .= $parser->indent();
        $source .= 'end';
        $source .= PHP_EOL;

        return $source;
    }

    public function injectScope(&$parent_scope)
    {
        // Try to locate the class/shape
        $unqualified_first = $this->formatQualifiedName($this->class_or_shape);
        $first = $parent_scope->lookup($unqualified_first);

        if (null === $first) {
            $kind = Tag::T_CLASS === $this->type ? 'Class' : 'Shape';
            throw new ScopeError(Localization::message('SCO190', [$kind, $unqualified_first]));
        }

        // Check type for the symbols
        if (Tag::T_SHAPE === $this->type) {
            // When it is not a shape
            if (~$first & Kind::K_SHAPE) {
                throw new ScopeError(Localization::message('SCO220', [$unqualified_first]));
            }
        } else {
            // Continue, assert this is a class and locate the shape
            if (~$first & Kind::K_CLASS) {
                throw new ScopeError(Localization::message('SCO200', [$unqualified_first]));
            }

            $shape_name = $this->formatQualifiedName($this->class_for);
            $shape = $parent_scope->lookup($shape_name);

            // Shape not declared
            if (null === $shape) {
                throw new ScopeError(Localization::message('SCO210', [$shape_name]));
            }

            // Not a shape
            if (~$shape & Kind::K_SHAPE) {
                throw new ScopeError(Localization::message('SCO220', [$shape_name]));
            }
        }

        $this->scope = new Scope($parent_scope);
        foreach ($this->body->stmt_list as $node) {
            if ($node instanceof FnStmt) {
                $node->flagBindSelf();
            }

            $node->injectScope($this->scope);
        }
    }

    public function runTypeChecker()
    {
        // TODO
    }
}
