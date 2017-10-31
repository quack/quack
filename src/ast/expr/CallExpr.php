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
        $callee = $this->callee->getType();
        if (!($callee instanceof FunctionType)) {
            throw new TypeError(Localization::message('TYP310', [$callee_type]));
        }

        if ($called_with_argc > count($callee->parameters)) {
            // Too many parameters provided to the function. Stop.
            throw new TypeError(Localization::message('TYP450', [$callee]));
        }

        $index = 0;
        $result_type = $callee;
        foreach ($this->arguments as $argument) {
            $expected = $callee->parameters[$index];
            $received = $argument->getType();
            if ($expected->is_generic) {
                // Bind to function scope when generic
                $this->scope->insert($expected->name, Symbol::S_VARIABLE | Symbol::S_DATA_PARAM);
                $this->scope->setMeta(Meta::M_TYPE, $expected->name, $received);
            }

            $expected->bindScope($this->scope);
            if (!$expected->check($received)) {
                // When this parameter doesn't match the expected by the function
                throw new TypeError(Localization::message('TYP330', [$index + 1, $expected, $received]));
            }
            $index++;
            $parameters = array_slice($callee->parameters, $index, $called_with_argc);
            $return_type = $callee->return->fill($this->scope);

            if (count($parameters) === 0) {
                // When no more parameters to reduce, compute return
                $result_type = $return_type;
            } else {
                $result_type = new FunctionType($parameters, $return_type);
            }
        }

        return $result_type;
    }
}
