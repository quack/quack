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
namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Ast\Types\FunctionType;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;

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

        if (count($this->body->body) > 0) {
            $source .= PHP_EOL;
            $source .= $this->body->format($parser);
            $source .= $parser->indent();
        }

        $source .= '}';

        return $this->parenthesize($source);
    }

    public function injectScope($parent_scope)
    {
        $this->scope = new Scope($parent_scope);
        $this->body->injectScope($this->scope);
    }

    public function getType()
    {
        $empty_return = $this->scope->getPrimitiveType('Empty');
        return new FunctionType([], $empty_return, []);
    }
}
