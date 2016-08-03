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
namespace QuackCompiler\Ast;

use \QuackCompiler\Ast\Stmt\ConstStmt;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\ScopeError;

use \ReflectionClass;

abstract class Node
{
    abstract public function format(Parser $parser);
    // TODO abstract public function injectScope(&$parent_scope);

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

            $bitfield = Kind::K_VARIABLE;
            if (null !== $value) {
                $bitfield |= Kind::K_INITIALIZED;
            }
            if (!($var instanceof ConstStmt)) {
                $bitfield |= Kind::K_MUTABLE;
            }

            $this->scope->insert($name, $bitfield);
        }
    }

    private function bindDecl($named_node, $type, $kind)
    {
        if ($this->scope->hasLocal($named_node->name)) {
            throw new ScopeError([
                'message' => "Symbol for {$type} `{$named_node->name}' declared twice"
            ]);
        }

        // When it is a function and it is marked as public
        if (Kind::K_FUNCTION === $kind && $node->is_pub) {
            $kind |= Kind::K_PUB;
        }

        $this->scope->insert($named_node->name, $kind);
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
                case 'MemberStmt':
                    $this->bindVariableDecl($node);
                    break;
                case 'FnStmt':
                    $this->bindDecl($node, 'function', Kind::K_FUNCTION);
                    break;
                case 'BlueprintStmt':
                    $this->bindDecl($node, 'blueprint', Kind::K_BLUEPRINT);
                    break;
                case 'EnumStmt':
                    $this->bindDecl($node, 'enum', Kind::K_ENUM);
                    break;
                case 'TraitStmt':
                    $this->bindDecl($node, 'trait', Kind::K_TRAIT);
                    break;
                case 'StructStmt':
                    $this->bindDecl($node, 'struct', Kind::K_STRUCT);
                    break;
            }
        }
    }
}
