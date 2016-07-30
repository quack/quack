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

use \QuackCompiler\Ast\Expr;
use \QuackCompiler\Ast\Stmt;

/**
 * This piece specifies, currently, how the scope control works in Quack.
 * We have an initial global symbol table, that comes by default to this class,
 * ok?
 * The lookup is made by reference (bottom-up), *EVERY* scope *MUST* have a
 * &parent, except by the *GLOBAL* scope, right?
 * Later, we'll need to keep track of the line and columns, but, currently,
 * let's just ignore them, otherwise, it'll be very hard to analyse the AST
 * visually.
 * I'll make dozens of prototypes until I have something really efficient, not
 * a workaround-traversal.
 *
 * RULES
 *
 * The following expressions may contain own scopes:
 *  -   LambdaExpr
 *  -   WhereExpr
 * However, as much as expr ::= lambda-expr, we must traverse all expr anyway,
 * ALL. ALL!
 *
 * Unlike JS, Quack has own scope for blocks!
 *
 * The following statements may contain own scopes:
 *  -   BlockStmt, ElifStmt (elif), FnStmt, ForStmt, ForeachStmt
 *  -   IfStmt (if/else), ImplStmt, (StructStmt TraitStmt?), TryStmt
 *  -   WhileStmt
 *  -   BlueprintStmt (this is an exception, we must take a special care with it)
 *  -   CaseStmt (not applied to Switch, only to case)
 *
 * The other statements don't need to be traversed over (and cannot), only their
 * expressions
 *
 * The following nodes may create symbols:
 *  -   WhereExpr, BlueprintStmt
 *  -   FnStmt, StructStmt
 *  -   MemberStmt, TraitStmt
 *  -   LetStmt
 *  -   ConstStmt
 */
class ScopeInjector
{
    private $ast;
    private $global_scope;

    public function __construct($ast, $global_scope)
    {
        $this->ast = $ast;
        $this->global_scope = $global_scope;
    }

    public function process()
    {
        $this->traverse($this->ast, $this->global_scope);
        return $this->ast;
    }

    private function traverse(&$node, Scope &$parent)
    {
        // Bind scope (for everything that satisfies the interface!)
        if ($node instanceof Stmt\Stmt && $node->shouldHaveOwnScope()) {
            $node->createScopeWithParent($parent);

            // When it is a function, pre-inject its parameters
            if ($node instanceof Stmt\FnStmt) {
                foreach ($node->parameters as $item) {
                    $param = (object) $item;
                    if ($node->scope->symbolInScope($param->name)) {
                        throw new ScopeError(['message' => "Parameter `{$param->name}' declared twice for function {$node->name}"]);
                    }
                    $node->scope->insert($param->name, [
                        'initialized' => true,
                        'kind'        => 'variable|parameter',
                        'mutable'     => false
                    ]);
                }
            }

            foreach ($node->getStmtList() as $sub) {

                if ($sub instanceof Stmt\LetStmt || $sub instanceof Stmt\ConstStmt) {
                    foreach ($sub->definitions as $def) {
                        if ($node->scope->symbolInScope($def[0])) {
                            throw new ScopeError(['message' => "Symbol `{$def[0]}' declared twice"]);
                        }

                        $node->scope->insert($def[0], [
                            'initialized' => null !== $def[1],
                            'kind'        => 'variable',
                            'mutable'     => $sub instanceof Stmt\LetStmt
                        ]);
                    }
                }

                if ($sub instanceof Stmt\FnStmt) {
                    if ($node->scope->symbolInScope($sub->name)) {
                        throw new ScopeError(['message' => "Symbol for function `{$sub->name}' declared twice"]);
                    }
                    $node->scope->insert($sub->name, [
                        'initialized' => true,
                        'kind'        => 'function',
                        'mutable'     => false
                    ]);
                }
            }
        }

        // Continue traversing sub-elements and deal with exceptions (1/1!)
        if ($node instanceof Stmt\BlockStmt || $node instanceof Stmt\ProgramStmt
            || $node instanceof Stmt\WhileStmt || $node instanceof Stmt\FnStmt) {
            foreach ($node->getStmtList() as $sub) {
                $this->traverse($sub, $node->scope);
            }
        }
    }
}
