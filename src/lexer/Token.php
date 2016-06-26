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
namespace QuackCompiler\Lexer;

class Token
{
    private $tag;
    private $pointer;
    private $symbol_table;

    public function __construct($tag, $pointer = null)
    {
        $this->tag = $tag;
        $this->pointer = $pointer;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function getPointer()
    {
        return $this->pointer;
    }

    public function __toString()
    {
        if (isset($this->pointer)) {
            $tag_name = Tag::getName($this->tag);
            return isset($this->symbol_table)
                ? "[" . $tag_name . ", " . $this->symbol_table->get($this->pointer) . "]"
                : "[" . $tag_name . ", " . $this->pointer . "]";
        }
        return "[" . $this->tag . "]";
    }

    public function showSymbolTable(SymbolTable &$symbol_table)
    {
        $this->symbol_table = $symbol_table;
    }
}
