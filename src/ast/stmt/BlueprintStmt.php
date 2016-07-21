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

class BlueprintStmt implements Stmt
{
    public $name;
    public $extends;
    public $implements;
    public $body;

    public function __construct($name, $extends, $implements, $body)
    {
        $this->name = $name;
        $this->extends = $extends;
        $this->implements = $implements;
        $this->body = $body;
    }

    public function format(Parser $parser)
    {
        $source = 'blueprint ';
        $source .= $this->name;

        if (null !== $this->extends) {
            $source .= ' : ';
            $source .= implode('.', $this->extends);
        }

        if (sizeof($this->implements) > 0) {
            $source .= ' # ';
            $source .= implode('; ', array_map(function ($class) {
                return implode('.', $class);
            }, $this->implements));
        }

        $source .= PHP_EOL;

        $parser->openScope();

        foreach ($this->body as $stmt) {
            $source .= $parser->indent();
            $source .= $stmt->format($parser);
        }

        $parser->closeScope();

        $source .= $parser->indent();
        $source .= 'end';
        $source .= PHP_EOL;

        return $source;
    }
}
