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

use \QuackCompiler\Ast\Stmt\BlockStmt;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Types\NativeQuackType;
use \QuackCompiler\Types\TypeError;

class IfStmt extends Stmt
{
    public $condition;
    public $body;
    public $elif;
    public $else;

    public function __construct($condition, $body, $elif, $else)
    {
        $this->condition = $condition;
        $this->body = $body;
        $this->elif = $elif;
        $this->else = $else;
    }

    public function format(Parser $parser)
    {
        $source = 'if ';
        $source .= $this->condition->format($parser);
        $source .= PHP_EOL;
        $parser->openScope();
        $source .= $this->body->format($parser);
        $parser->closeScope();

        foreach ($this->elif as $elif) {
            $source .= $elif->format($parser);
        }

        if (null !== $this->else) {
            $source .= $parser->indent();
            $source .= 'else';
            $source .= PHP_EOL;
            $parser->openScope();
            $source .= $this->else->format($parser);
            $parser->closeScope();
        }

        $source .= $parser->indent();
        $source .= 'end';
        $source .= PHP_EOL;

        return $source;
    }

    public function injectScope(&$parent_scope)
    {
        // Bind scope in the body of if-statement
        $this->body->scope = new Scope($parent_scope);
        $this->condition->injectScope($parent_scope);

        foreach ($this->body->stmt_list as $node) {
            $node->injectScope($this->body->scope);
        }

        // Bind scope for every elif. This class is just a
        // bridge for that
        foreach ($this->elif as $elif) {
            $elif->injectScope($parent_scope);
        }

        // If we have `else', bind in depth
        if (null !== $this->else) {
            $this->else->scope = new Scope($parent_scope);

            foreach ($this->else->stmt_list as $node) {
                $node->injectScope($this->else->scope);
            }
        }
    }

    public function runTypeChecker()
    {
        $condition_type = $this->condition->getType();
        if (NativeQuackType::T_BOOL !== $condition_type->code) {
            throw new TypeError(Localization::message('TYP140', [$condition_type]));
        }

        $this->body->runTypeChecker();

        foreach ($this->elif as $elif) {
            $elif->runTypeChecker();
        }

        if (null !== $this->else) {
            $this->else->runTypeChecker();
        }
    }
}
