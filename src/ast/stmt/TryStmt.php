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

class TryStmt implements Stmt
{
    public $try;
    public $rescues;
    public $finally;

    public function __construct($try, $rescues, $finally)
    {
        $this->try = $try;
        $this->rescues = $rescues;
        $this->finally = $finally;
    }

    public function format(Parser $parser)
    {
        $source = 'try';
        $source .= PHP_EOL;

        $parser->openScope();

        foreach ($this->try as $stmt) {
            $source .= $parser->indent();
            $source .= $stmt->format($parser);
        }

        $parser->closeScope();

        foreach ($this->rescues as $rescue) {
            $obj = (object) $rescue;
            $source .= $parser->indent();
            $source .= 'rescue [';
            $source .= implode('.', $obj->exception_class);
            $source .= ' ';
            $source .= $obj->variable;
            $source .= ']';
            $source .= PHP_EOL;

            $parser->openScope();

            foreach ($obj->body as $stmt) {
                $source .= $parser->indent();
                $source .= $stmt->format($parser);
            }

            $parser->closeScope();
        }

        if (null !== $this->finally) {
            $source .= $parser->indent();
            $source .= 'finally ';
            $source .= PHP_EOL;

            $parser->openScope();

            foreach ($this->finally as $stmt) {
                $source .= $parser->indent();
                $source .= $stmt->format($parser);
            }

            $parser->closeScope();
        }

        $source .= $parser->indent();
        $source .= 'end';
        $source .= PHP_EOL;

        return $source;
    }
}
