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

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\ScopeError;

class ImplStmt extends Stmt
{
    public $type;
    public $trait_or_shape;

    public $trait_for;
    public $body;

    public function __construct($type, $trait_or_shape, $trait_for, $body)
    {
        $this->type = $type;
        $this->trait_or_shape = $trait_or_shape;
        $this->trait_for = $trait_for;
        $this->body = $body;
    }

    private function formatQualifiedName($name)
    {
        return implode('.', $name);
    }

    public function format(Parser $parser)
    {
        $source = 'impl ';
        $source .= $this->formatQualifiedName($this->trait_or_shape);

        if (Tag::T_TRAIT === $this->type) {
            $source .= ' for ';
            $source .= $this->formatQualifiedName($this->trait_for);
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
        // Try to locate the trait/shape
        $unqualified_first = $this->formatQualifiedName($this->trait_or_shape);
        $first = $parent_scope->lookup($unqualified_first);
        $type = Tag::T_TRAIT === $this->type
            ? 'trait'
            : 'shape';

        if (null === $first) {
            throw new ScopeError([
                'message' => ucfirst($type) . " `{$unqualified_first}' not found"
            ]);
        }

        // Check type for the symbols
        if ('shape' === $type) {
            if (!($first & Kind::K_SHAPE)) {
                throw new ScopeError([
                    'message' => "`{$unqualified_first}' is not a shape"
                ]);
            }
        } else {
            // Continue, assert this is a trait and locate the shape
            if (!($first & Kind::K_TRAIT)) {
                throw new ScopeError([
                    'message' => "`{$unqualified_first}' is not a trait"
                ]);
            }

            $shape_name = $this->formatQualifiedName($this->trait_for);
            $shape = $parent_scope->lookup($shape_name);

            // Shape not declared
            if (null === $shape) {
                throw new ScopeError([
                    'message' => "Shape `{$shape_name}' not found"
                ]);
            }

            // Not a shape
            if (!($shape & Kind::K_SHAPE)) {
                throw new ScopeError([
                    'message' => "`{$shape_name}' is not a shape"
                ]);
            }
        }

        $this->createScopeWithParent($parent_scope);
        $this->bindDeclarations($this->body->stmt_list);

        foreach ($this->body->stmt_list as $node) {
            if ($node instanceof FnStmt) {
                $node->flagBindSelf();
            }

            $node->injectScope($this->scope);
        }
    }
}
