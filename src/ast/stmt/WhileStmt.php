<?php
/**
 * Quack Compiler and toolkit
 * Copyright (C) 2015-2017 Quack and CONTRIBUTORS
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
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Types\TypeError;

class WhileStmt extends Stmt
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
        $source = 'while ';
        $source .= $this->condition->format($parser);
        $source .= PHP_EOL;
        $source .= $this->body->format($parser);
        $source .= $parser->indent();
        $source .= 'end' . PHP_EOL;

        return $source;
    }

    public function injectScope($parent_scope)
    {
        $this->scope = new Scope($parent_scope);
        $this->scope->setMetaInContext(Meta::M_LABEL, Meta::nextMetaLabel());
        $this->condition->injectScope($parent_scope);
        $this->body->injectScope($this->scope);
    }

    public function runTypeChecker()
    {
        $bool = $this->scope->getPrimitiveType('Bool');
        $condition_type = $this->condition->getType();
        if (!$bool->check($condition_type)) {
            throw new TypeError(Localization::message('TYP010', [$condition_type]));
        }

        $this->body->runTypeChecker();
    }
}
