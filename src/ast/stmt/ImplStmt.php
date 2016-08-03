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

use \QuackCompiler\Scope\ScopeError;

class ImplStmt extends Stmt
{
    public $type;
    public $trait_or_struct;
    public $trait_for;
    public $body;

    public function __construct($type, $trait_or_struct, $trait_for, $body)
    {
        $this->type = $type;
        $this->trait_or_struct = $trait_or_struct;
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
        $source .= $this->formatQualifiedName($this->trait_or_struct);

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
        // Try to locate the trait/struct
        $unqualified_first = $this->formatQualifiedName($this->trait_or_struct);
        $first = $parent_scope->lookup($unqualified_first);
        $type = Tag::T_TRAIT === $this->type
            ? 'trait'
            : 'struct';

        if (null === $first) {
            throw new ScopeError([
                'message' => ucfirst($type) . " `{$unqualified_first}' not found"
            ]);
        }

        // Check type for the symbols
        if ('struct' === $type) {
            if ('struct' !== $first['kind']) {
                throw new ScopeError([
                    'message' => "`{$unqualified_first}' is not a struct"
                ]);
            }
        } else {
            // Continue, assert this is a trait and locate the struct
            if ('trait' !== $first['kind']) {
                throw new ScopeError([
                    'message' => "`{$unqualified_first}' is not a trait"
                ]);
            }

            $struct_name = $this->formatQualifiedName($this->trait_for);
            $struct = $parent_scope->lookup($struct_name);

            // Struct not declared
            if (null === $struct) {
                throw new ScopeError([
                    'message' => "Struct `{$struct_name}' not found"
                ]);
            }

            // Not a struct
            if ('struct' !== $struct['kind']) {
                throw new ScopeError([
                    'message' => "`{$struct_name}' is not a struct"
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
