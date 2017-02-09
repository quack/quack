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

use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\ScopeError;

class SwitchStmt extends Stmt
{
    public $value;
    public $cases;

    public function __construct($value, $cases)
    {
        $this->value = $value;
        $this->cases = $cases;
    }

    public function format(Parser $parser)
    {
        $source = 'switch ';
        $source .= $this->value->format($parser);
        $source .= PHP_EOL;

        $parser->openScope();

        foreach ($this->cases as $case) {
            $source .= $parser->indent();
            $source .= $case->format($parser);
        }

        $parser->closeScope();

        $source .= $parser->indent();
        $source .= 'end';
        $source .= PHP_EOL;

        return $source;
    }

    public function injectScope(&$parent_scope)
    {
        $this->value->injectScope($parent_scope);
        // Just act like a bridge for cases
        foreach ($this->cases as $case) {
            $case->injectScope($parent_scope);
        }
    }

    public function runTypeChecker()
    {
        $value_type = $this->value->getType();
        $else_counter = 0;
        foreach ($this->cases as $case) {
            if (!$case->is_else) {
                $case_type = $case->value->getType();
                if (!$case_type->isExactlySameAs($value_type)) {
                    $case_value = property_exists($case->value, 'value')
                        ? "`{$case->value->value}' "
                        : "";

                    throw new ScopeError([
                        'message' => "Expecting type of case {$case_value}of `switch' statement to be `$value_type'. Got `{$case_type}'"
                    ]);
                }
            } else {
                $else_counter++;
            }

            $case->runTypeChecker();
        }

        if ($else_counter > 1) {
            throw new ScopeError([
                'message' => "More than one `else' clause for `switch' statement"
            ]);
        }
    }
}
