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
    private $content;

    // Carries all the metadata about the tokens based in key => value
    // Obs.: Currently used only to disambiguate ' from "
    public $metadata = [];

    public function __construct($tag, $content = null)
    {
        $this->tag = $tag;
        $this->content = $content;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function __toString()
    {
        if (!is_null($this->content)) {
            $tag_name = Tag::getName($this->tag);
            return "[" . $tag_name . ", " . $this->content . "]";
        }

        return "[" . $this->tag . "]";
    }
}
