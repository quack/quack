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
namespace QuackCompiler\Parser;

use \Exception;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;

define('BEGIN_RED', "\033[01;31m");
define('END_RED', "\033[0m");

define('BEGIN_GREEN', "\033[01;33m");
define('END_GREEN', "\033[0m");

define('BEGIN_BG_RED', "\033[01;41m");
define('END_BG_RED', "\033[0m");

define('BEGIN_BOLD', "\033[1m");
define('END_BOLD', "\033[0m");

class SyntaxError extends Exception
{
    private $expected;
    private $found;
    private $parser;

    public function __construct($parameters)
    {
        $this->expected = $parameters['expected'];
        $this->found    = $parameters['found'];
        $this->parser   = $parameters['parser'];
    }

    private function extractPieceOfSource()
    {
        // TODO: Apply a better error handler. The column should go (n)
        // chars back when the expected element does not match
        return "";
    }

    public function __toString()
    {
        $source = $this->extractPieceOfSource();
        $expected = $this->getExpectedTokenName();
        $found = $this->getFoundTokenName();
        $position = $this->getPosition();

        return $source . PHP_EOL . implode(PHP_EOL, [
            BEGIN_RED,
            "*** You have a syntactic error in your code!",
            "    Expecting [{$expected}]",
            "    Found     [{$found}]",
            "    Line      {$position['line']}",
            "    Column    {$position['column']}",
            END_RED
        ]);
    }

    private function getExpectedTokenName()
    {
        return is_integer($this->expected)
            ? Tag::getName($this->expected)
            : $this->expected;
    }

    private function getFoundTokenName()
    {
        $found_tag = $this->found->getTag();

        return 0 === $found_tag
            ? "end of the source"
            : Tag::getName($found_tag) ?: $found_tag;
    }

    private function getOriginalSource()
    {
        return $this->parser->input;
    }

    private function getPosition()
    {
        return $this->parser->position();
    }
}
