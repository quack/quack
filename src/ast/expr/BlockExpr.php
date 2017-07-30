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
namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Ast\Types\LiteralType;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Types\NativeQuackType;
use \QuackCompiler\Types\Type;

class BlockExpr extends Expr
{
    public $body;

    public function __construct($body)
    {
        $this->body = $body;
    }

    public function format(Parser $parser)
    {
        $source = '&{';

        if (sizeof($this->body->stmt_list) > 0) {
            $source .= PHP_EOL;
            $parser->openScope();
            $source .= $this->body->format($parser);
            $parser->closeScope();
            $source .= $parser->indent();
        }

        $source .= '}';

        return $this->parenthesize($source);
    }

    public function injectScope(&$parent_scope)
    {
        $this->scope = new Scope($parent_scope);
        foreach ($this->body->stmt_list as $node) {
            $node->injectScope($this->scope);
        }
    }

    public function getType()
    {
        return new LiteralType(NativeQuackType::T_BLOCK);
    }
}
