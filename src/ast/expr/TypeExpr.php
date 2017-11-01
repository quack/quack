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

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Types\TaggedUnion;
use \QuackCompiler\Types\TypeError;

class TypeExpr extends Expr
{
    private $name;
    private $values;

    public function __construct($name, $values)
    {
        $this->name = $name;
        $this->values = $values;
    }

    public function format(Parser $parser)
    {
        $source = $this->name;
        if (count($this->values) > 0) {
            $source .= '(';
            $source .= implode(', ', array_map(function ($expr) use ($parser) {
                return $expr->format($parser);
            }, $this->values));
            $source .= ')';
        }

        return $source;
    }

    public function injectScope($scope)
    {
        $this->scope = $scope;
        foreach ($this->values as $value) {
            $value->injectScope($scope);
        }
    }

    public function getType()
    {
        $cons = $this->scope->lookup($this->name);
        // Ensure the type is declared
        if (null === $cons) {
            throw new TypeError(Localization::message('TYP120', [$this->name]));
        }

        // Ensure it is member of a data
        if (~$cons & Symbol::S_DATA_MEMBER) {
            throw new TypeError(Localization::message('TYP200', [$this->name]));
        }

        // Get data of this type, parameters to bind and the constraint
        // to satisfy
        $data = $this->scope->getMeta(Meta::M_TYPE, $this->name);
        $parameters = $data->getParameters();
        $constraint = $data->getConstraint($this->name);

        // Throw error when too much parameters
        if (count($this->values) > count($constraint)) {
            throw new TypeError(Localization::message('TYP210', [$this->name]));
        }

        // TODO: Fill the parameters and auto-curry
        return $data;
    }
}
