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

class ExtensionStmt implements Stmt
{
    public $appliesTo;
    public $appliesToRegexes;
    public $implements;
    public $body;

    public function __construct($appliesTo, $appliesToRegexes, $implements, $body)
    {
        $this->appliesTo = $appliesTo;
        $this->appliesToRegexes = $appliesToRegexes;
        $this->implements = $implements;
        $this->body = $body;
    }

    public function format(Parser $parser)
    {
        $source = 'extension for ';

        $source .= implode('; ', array_map(function ($param) {
            return implode('', $param);
        }, $this->appliesTo));

        $source .= implode('; ', array_map(function ($param) {
            return $param->format($parser);
        }, $this->appliesToRegexes));

        if (count($this->implements) > 0) {
            $source .= ' # ';
            $source .= implode('; ', array_map(function ($param) {
                return implode('', $param);
            }, $this->implements));
        }

        foreach ($this->body as $node) {
            $source .= $node->format($parser);
        }

        $source .= ' end'. PHP_EOL;

        return $source;
    }
}
