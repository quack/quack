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

use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Pretty\CliColorizer;
use \QuackCompiler\Types\ParametricTypes;

class ExprStmt extends Stmt
{
    public $expr;

    public function __construct($expr)
    {
        ParametricTypes::reset();
        $this->expr = $expr;
    }

    public function format(Parser $parser)
    {
        return 'do ' . $this->expr->format($parser) . PHP_EOL;
    }

    public function injectScope($parent_scope)
    {
        $this->expr->injectScope($parent_scope);
    }

    public function runTypeChecker()
    {
        $type = $this->expr->getType();
        if ('1' === getenv('QUACK_DEV')) {
            echo $type->render(new CliColorizer()), PHP_EOL;
        } else {
            var_dump((string) $type);
        }
    }
}
