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
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Scope\Kind;

class ShapeStmt extends Stmt
{
    public $name;
    public $members;

    public function __construct($name, $members)
    {
        $this->name = $name;
        $this->members = $members;
    }

    public function format(Parser $parser)
    {
        $source = 'shape ';
        $source .= $this->name;
        $source .= PHP_EOL;

        $parser->openScope();

        foreach ($this->members as $member) {
            $source .= $parser->indent();
            $source .= $member;
            $source .= PHP_EOL;
        }

        $parser->closeScope();

        $source .= $parser->indent();
        $source .= 'end';
        $source .= PHP_EOL;

        return $source;
    }

    public function injectScope(&$parent_scope)
    {
        $this->createScopeWithParent($parent_scope);

        foreach ($this->members as $member) {
            if ($this->scope->hasLocal($member)) {
                throw new ScopeError(Localization::message('SCO110', [$member, $this->name]));
            }

            $this->scope->insert($member, Kind::K_INITIALIZED | Kind::K_MEMBER);
        }
    }

    public function runTypeChecker()
    {
        // TODO: Implement type checking for shape
    }
}
