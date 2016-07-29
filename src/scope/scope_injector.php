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
namespace QuackCompiler\Scope;

/**
 * TEMP:
 * Scope rules
 * - The statements container should track the sub-statement bindings
 * [ADD MORE HERE, WHEN NECESSARY]
 *
 * We need also to find a way to track the lines and columns, because
 * we don't have this info after the tree is generated. After, I'll make
 * all the nodes of the tree carry information about this state, so, we can
 * show the error directly in the input. Currently, let's throw simple
 * exceptions
 */
namespace QuackCompiler\Scope;

use \QuackCompiler\Ast\Expr;
use \QuackCompiler\Ast\Stmt;

use \Exception as ScopeViolation;

function has_bound_scope($node)
{
    return isset($node->scope) && $node->scope instanceof Scope;
}

function bind_scope_if_unbound(&$node, &$scope)
{
    if (!has_bound_scope($node)) {
        $node->scope = new Scope;
        $node->parent = &$scope; // Comment this line for a better visualization
    }
}

function scope_injector(&$node, Scope &$parent_scope)
{
    if (is_array($node)) {
        foreach ($node as $stmt) {
            scope_injector($stmt, $parent_scope);
        }

        // Verify duplication and violations after scope injection
        $scope_variables = [];

        foreach ($node as $stmt) {
            if (isset($stmt->scope)) {
                // Lookup for statements that have own scope (ensure immutability)
                foreach ($stmt->scope->table as $symbol => $metadata) {
                    // When found and declared as variable
                    if (in_array($symbol, $scope_variables, true)) {
                        if ('LetStmt' === $metadata['node'] || 'ConstStmt' === $metadata['node']) {
                            throw new ScopeViolation("Variable declared twice in scope: {$symbol}");
                        }
                    }

                    $scope_variables[] = $symbol;
                }
            }
        }

        return;
    }

    if ($node instanceof Stmt\LetStmt) {
        bind_let($node, $parent_scope);
    } elseif ($node instanceof Stmt\ConstStmt) {
        bind_const($node, $parent_scope);
    }
}

function bind_let(&$node, &$parent_scope)
{
    $bound_names = [];
    bind_scope_if_unbound($node, $parent_scope);

    foreach ($node->definitions as $def) {
        if (in_array($def[0], $bound_names, true)) {
            throw new ScopeError([
                'begin' => $def->begin,
                'end'   => $def->end
            ]);
        }

        $bound_names[] = $def[0];
        $node->scope->insert($def[0], [
            'initialized' => null !== $def[1],
            'node'        => 'LetStmt',
            'mutable'     => true
        ]);
    }
}

function bind_const(&$node, &$parent_scope)
{
    $bound_names = [];
    bind_scope_if_unbound($node, $parent_scope);

    foreach ($node->definitions as $def) {
        if (in_array($def[0], $bound_names, true)) {
            throw new ScopeViolation("Constant violation: {$def[0]}");
        }

        $bound_names[] = $def[0];
        $node->scope->insert($def[0], [
            'initialized' => true,
            'node'        => 'ConstStmt',
            'mutable'     => false
        ]);
    }
}
