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

use \QuackCompiler\Parser\Parser;

class LambdaExpr extends Expr
{
    public $by_reference;
    public $parameters;
    public $type;
    public $body;
    public $lexical_vars;

    public function __construct($by_reference, $parameters, $type, $body, $lexical_vars)
    {
        $this->by_reference = $by_reference;
        $this->parameters = $parameters;
        $this->type = $type;
        $this->body = $body;
        $this->lexical_vars = $lexical_vars;
    }

    public function format(Parser $parser)
    {
        $source = 'fn ';

        if ($this->by_reference) {
            $source .= '* ';
        }

        $source .= '{ ';

        $source .= implode('; ', array_map(function ($param) {
            $obj = (object) $param;

            $source = '';
            $obj->ellipsis && $source .= '... ';
            $obj->by_reference && $source .= '*';
            $source .= $obj->name;
            return $source;
        }, $this->parameters));

        $source .= 'fn { ' !== $source ? ' | ' : '| ';

        // (*self).type determines whether the lambda expression holds an
        // expression or a statement
        // TODO: Implement support for statements on code generation
        $source .= $this->body->format($parser);
        $source .= ' }';

        $size_t_lexical_vars = sizeof($this->lexical_vars);

        if ($size_t_lexical_vars > 0) {
            $source .= ' in ';
            $source .= 1 === $size_t_lexical_vars
                ? $this->lexical_vars[0]
                : (
                    '{ ' .
                    implode('; ', $this->lexical_vars) .
                    ' }'
                );
        }

        if ($this->parenthesize) {
            $source = '(' . $source . ')';
        }

        return $source;
    }
}
