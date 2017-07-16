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

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parser\Parser;

class OperatorStmt extends Stmt
{
    public $type;
    public $operator;
    public $precedence;

    public function __construct($type, $operator, $precedence)
    {
        $this->type = $type;
        $this->operator = $operator;
        $this->precedence = $precedence;
    }

    public function format(Parser $parser)
    {
        $names = [
            Tag::T_PREFIX => 'prefix ',
            Tag::T_INFIXL => 'infixl ',
            Tag::T_INFIXR => 'infixr '
        ];

        $source = $names[$this->type];

        if (null !== $this->precedence) {
            $source .= (string) $this->precedence;
            $source .= ' ';
        }

        $source .= '&(' . $this->operator . ')';
        $source .= PHP_EOL;
        return $source;
    }

    public function injectScope(&$parent_scope)
    {
        // TODO inject scope for operator stmt, check if operator is not
        // previously defined in context
    }

    public function runTypeChecker()
    {
        // Pass
    }
}
