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
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Types\NativeQuackType;
use \QuackCompiler\Types\TypeError;

class ElifStmt extends Stmt
{
    public $condition;
    public $body;

    public function __construct($condition, $body)
    {
        $this->condition = $condition;
        $this->body = $body;
    }

    public function format(Parser $parser)
    {
        $source = $parser->indent();
        $source .= 'elif ';
        $source .= $this->condition->format($parser);
        $source .= PHP_EOL;

        $parser->openScope();

        foreach ($this->body as $stmt) {
            $source .= $parser->indent();
            $source .= $stmt->format($parser);
        }

        $parser->closeScope();

        return $source;
    }

    public function injectScope(&$parent_scope)
    {
        $this->scope = new Scope($parent_scope);
        $this->condition->injectScope($parent_scope);

        foreach ($this->body as $node) {
            $node->injectScope($this->scope);
        }
    }

    public function runTypeChecker()
    {
        $condition_type = $this->condition->getType();
        if (NativeQuackType::T_BOOL !== $condition_type->code) {
            throw new TypeError(Localization::message('TYP180', [$condition_type]));
        }

        foreach ($this->body as $stmt) {
            $stmt->runTypeChecker();
        }
    }
}
