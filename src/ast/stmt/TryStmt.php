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

use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\ScopeError;

class TryStmt extends Stmt
{
    public $try;
    public $rescues;
    public $finally;

    public function __construct($try, $rescues, $finally)
    {
        $this->try = $try;
        $this->rescues = $rescues;
        $this->finally = $finally;
    }

    public function format(Parser $parser)
    {
        $source = 'try';
        $source .= PHP_EOL;

        $parser->openScope();

        $source .= $this->try->format($parser);

        $parser->closeScope();

        foreach ($this->rescues as $rescue) {
            $obj = (object) $rescue;
            $source .= $parser->indent();
            $source .= 'rescue (';
            $source .= implode('.', $obj->exception_class);
            $source .= ' ';
            $source .= $obj->variable;
            $source .= ')';
            $source .= PHP_EOL;

            $parser->openScope();

            $source .= $obj->body->format($parser);

            $parser->closeScope();
        }

        if (null !== $this->finally) {
            $source .= $parser->indent();
            $source .= 'finally';
            $source .= PHP_EOL;

            $parser->openScope();

            $source .= $this->finally->format($parser);

            $parser->closeScope();
        }

        $source .= $parser->indent();
        $source .= 'end';
        $source .= PHP_EOL;

        return $source;
    }

    public function injectScope(&$parent_scope)
    {
        // Inject scope on try body
        $this->try->createScopeWithParent($parent_scope);
        $this->try->bindDeclarations($this->try->stmt_list);

        // Continue depth-based traversal on try body
        foreach ($this->try->stmt_list as $node) {
            $node->injectScope($this->try->scope);
        }

        // Inject scope in all the rescue cases
        foreach (array_map(function($item) {
            return (object) $item;
        }, $this->rescues) as $rescue) {
            $rescue->body->createScopeWithParent($parent_scope);

            // Pre-bind rescue variable
            $rescue->body->scope->insert($rescue->variable, Kind::K_VARIABLE | Kind::K_INITIALIZED);

            // Bind rescue body
            $rescue->body->bindDeclarations($rescue->body->stmt_list);

            // Traverse rescue body
            foreach ($rescue->body->stmt_list as $node) {
                $node->injectScope($rescue->body->scope);
            }
        }

        // When finally is provided, inject scope and traverse
        if (null !== $this->finally) {
            $this->finally->createScopeWithParent($parent_scope);
            $this->finally->bindDeclarations($this->finally->stmt_list);

            // Continue depth-based traversal
            foreach ($this->finally->stmt_list as $node) {
                $node->injectScope($this->finally->scope);
            }
        }
    }

    public function runTypeChecker()
    {
        $this->try->runTypeChecker();

        foreach (array_map(function($item) {
            return (object) $item;
        }, $this->rescues) as $rescue) {
            $rescue->body->runTypeChecker();
        }

        if (null !== $this->finally) {
            $this->finally->runTypeChecker();
        }
    }
}
