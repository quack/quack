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

use \QuackCompiler\Ast\Types\FunctionType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Types\TypeError;

class CallExpr extends Expr
{
    public $callee;
    public $arguments;

    public function __construct($callee, $arguments)
    {
        $this->callee = $callee;
        $this->arguments = $arguments;
    }

    public function format(Parser $parser)
    {
        $source = $this->callee->format($parser);
        $source .= '(';
        $source .= implode(', ', array_map(function(Expr $arg) use ($parser) {
            return $arg->format($parser);
        }, $this->arguments));
        $source .= ')';

        return $this->parenthesize($source);
    }

    public function injectScope($parent_scope)
    {
        $this->callee->injectScope($parent_scope);

        foreach ($this->arguments as $arg) {
            $arg->injectScope($parent_scope);
        }
    }

    public function getType()
    {
        $callee_type = $this->callee->getType();
        if (!($callee_type instanceof FunctionType)) {
            throw new TypeError(Localization::message('TYP310', [$callee_type]));
        }

        // Check parameters length
        $expected_arguments = sizeof($callee_type->parameters);
        $received_arguments = sizeof($this->arguments);

        if ($received_arguments !== $expected_arguments) {
            throw new TypeError(Localization::message('TYP320',
                [$callee_type, $expected_arguments, $received_arguments]));
        }

        // Check for each parameter type based on index
        for ($i = 0; $i < $expected_arguments; $i++) {
            $expected_type = $callee_type->parameters[$i];
            $received_type = $this->arguments[$i]->getType();

            if (!$expected_type->check($received_type)) {
                throw new TypeError(Localization::message('TYP330',
                    [$i + 1, $expected_type, $received_type]));
            }
        }

        return $callee_type->return;
    }
}
