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

use QuackCompiler\Ast\Node;
use QuackCompiler\Scope\Scope;
use QuackCompiler\Scope\ScopeError;

use \ReflectionClass;

abstract class Stmt extends Node
{
    public function createScopeWithParent(Scope &$parent)
    {
        $this->scope = new Scope;
        $this->scope->parent = &$parent;
    }

    private function bindVariableDecl($var)
    {
        foreach ($var->definitions as $def) {
            $name = &$def[0];
            $value = &$def[1];

            if ($this->scope->hasLocal($name)) {
                throw new ScopeError([
                    'message' => "Symbol `{$name}' declared twice"
                ]);
            }

            $this->scope->insert($name, [
                'initialized' => null !== $value,
                'kind'        => 'variable',
                'mutable'     => !($var instanceof ConstStmt)
            ]);
        }
    }

    private function bindFunctionDecl($func)
    {
        if ($this->scope->hasLocal($func->name)) {
            throw new ScopeError([
                'message' => "Symbol for function `{$func->name}' declared twice"
            ]);
        }

        $this->scope->insert($func->name, [
            'initialized' => true,
            'kind'        => 'function',
            'mutable'     => false
        ]);
    }

    private function getNodeType($node)
    {
        $reflect = new ReflectionClass($node);
        return $reflect->getShortName();
    }

    public function bindDeclarations($stmt_list)
    {
        foreach ($stmt_list as $node) {
            switch ($this->getNodeType($node)) {
                case 'LetStmt':
                case 'ConstStmt':
                    $this->bindVariableDecl($node);
                    break;
                case 'FnStmt':
                    $this->bindFunctionDecl($node);
                    break;
            }
        }
    }
}
