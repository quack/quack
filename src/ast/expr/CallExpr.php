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
use \QuackCompiler\Ast\Types\GenericType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\Symbol;
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
        $this->scope = new Scope($parent_scope);
        $this->callee->injectScope($this->scope);

        foreach ($this->arguments as $argument) {
            $argument->injectScope($this->scope);
        }
    }

    public function getType()
    {
        // TODO: TYP330 for parameter error
        $called_with_argc = count($this->arguments);
        $callee = $this->callee->getType();
        if (!($callee instanceof FunctionType)) {
            // Element is not callable
            throw new TypeError(Localization::message('TYP310', [$callee_type]));
        }

        if ($called_with_argc > count($callee->parameters)) {
            // Too many parameters provided to the function. Stop.
            throw new TypeError(Localization::message('TYP450', [$callee]));
        }

        return $callee->computeCall($this->arguments);
    }
}
