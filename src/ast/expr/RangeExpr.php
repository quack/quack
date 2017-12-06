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

use \QuackCompiler\Ast\Expr;
use \QuackCompiler\Ast\Node;
use \QuackCompiler\Ds\Set;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Pretty\Parenthesized;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Types\ListType;
use \QuackCompiler\Types\TypeError;
use \QuackCompiler\Types\Unification;

class RangeExpr extends Node implements Expr
{
    use Parenthesized;

    public $from;
    public $to;
    public $by;

    public function __construct($from, $to, $by)
    {
        $this->from = $from;
        $this->to = $to;
        $this->by = $by;
    }

    public function format(Parser $parser)
    {
        $source = $this->from->format($parser);
        $source .= ' .. ';
        $source .= $this->to->format($parser);

        if (null !== $this->by) {
            $source .= ' by ';
            $source .= $this->by->format($parser);
        }

        return $this->parenthesize($source);
    }

    public function injectScope($parent_scope)
    {
        $this->scope = $parent_scope;
        $this->from->injectScope($parent_scope);
        $this->to->injectScope($parent_scope);

        if (null !== $this->by) {
            $this->by->injectScope($parent_scope);
        }
    }

    public function analyze(Scope $scope, Set $non_generic)
    {
        $type = (object) [
            'from' => $this->from->analyze($scope, $non_generic),
            'to'   => $this->to->analyze($scope, $non_generic),
            'by'   => null !== $this->by ? $this->by->analyze($scope, $non_generic) : null
        ];

        $number = $scope->getPrimitiveType('Number');
        $throw_error_on = function($operand, $got) {
            throw new TypeError(Localization::message('TYP220', [$operand, $got]));
        };


        try {
            Unification::unify($type->from, $number);
        } catch (TypeError $error) {
            $throw_error_on('from', $type->from);
        }

        try {
            Unification::unify($type->to, $number);
        } catch (TypeError $error) {
            $throw_error_on('to', $type->to);
        }

        if (null !== $type->by) {
            try {
                Unification::unify($type->by, $number);
            } catch (TypeError $error) {
                $throw_error_on('by', $type->by);
            }
        }

        return new ListType($number);
    }
}
