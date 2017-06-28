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

use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Types\NativeQuackType;
use \QuackCompiler\Types\TypeError;

class PostConditionalStmt extends Stmt
{
    public $stmt;
    public $predicate;
    public $tag;

    public function __construct(Stmt $stmt, Expr $predicate, $tag)
    {
        $this->stmt = $stmt;
        $this->predicate = $predicate;
        $this->tag = $tag;
    }

    public function format(Parser $parser)
    {
        // Remove newline from statement when it exists
        $source = rtrim($this->stmt->format($parser), PHP_EOL);
        $source .= Tag::T_WHEN === $this->tag
            ? ' when '
            : ' unless ';
        $source .= $this->predicate->format($parser);
        $source .= PHP_EOL;
        return $source;
    }

    public function injectScope(&$parent_scope)
    {
        // As much as it is conditional, and there may be conditional variable
        // declarations, we must give to the post conditional an own scope
        $this->createScopeWithParent($parent_scope);
        $this->bindDeclarations([$this->stmt]);
        $this->predicate->injectScope($parent_scope);
        $this->stmt->injectScope($this->scope);
    }

    public function runTypeChecker()
    {
        $condition_type = $this->predicate->getType();
        if (NativeQuackType::T_BOOL !== $condition_type->code) {
            throw new TypeError(Localization::message('TYP030', [$condition_type]));
        }
    }
}
