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

abstract class Lexer
{
    const EOF      = -1;
    const EOF_TYPE =  0;

    public $input;
    public $position = 0;
    public $peek;

    protected $words = [];

    public $symbol_table;
    public $keywords_hash = [];

    public function __construct($input)
    {
        $this->size = strlen($input);

        if ($this->size === 0) {
            exit;
        }

        $this->symbol_table = new SymbolTable;
        $this->input = $input;
        $this->peek  = $input[0];

        // Reserve keywords
        $this->reserve(new Word(Tag::T_TRUE, "true"));
        $this->reserve(new Word(Tag::T_FALSE, "false"));
        $this->reserve(new Word(Tag::T_LET, "let"));
        $this->reserve(new Word(Tag::T_IF, "if"));
        $this->reserve(new Word(Tag::T_FOR, "for"));
        $this->reserve(new Word(Tag::T_WHILE, "while"));
        $this->reserve(new Word(Tag::T_DO, "do"));
        $this->reserve(new Word(Tag::T_INIT, "init"));
        $this->reserve(new Word(Tag::T_MODULE, "module"));
        $this->reserve(new Word(Tag::T_BLUEPRINT, "blueprint"));
        $this->reserve(new Word(Tag::T_EXTENSION, "extension"));
        $this->reserve(new Word(Tag::T_GOTO, "goto"));
        $this->reserve(new Word(Tag::T_FOREACH, "foreach"));
        $this->reserve(new Word(Tag::T_IN, "in"));
        $this->reserve(new Word(Tag::T_MOD, "mod"));
        $this->reserve(new Word(Tag::T_WHERE, "where"));
        $this->reserve(new Word(Tag::T_CONST, "const"));
        $this->reserve(new Word(Tag::T_NIL, "nil"));
        $this->reserve(new Word(Tag::T_OPEN, "open"));
        $this->reserve(new Word(Tag::T_GLOBAL, "global"));
        $this->reserve(new Word(Tag::T_AS, "as"));
        $this->reserve(new Word(Tag::T_ENUM, "enum"));
        $this->reserve(new Word(Tag::T_CONTINUE, "continue"));
        $this->reserve(new Word(Tag::T_SWITCH, "switch"));
        $this->reserve(new Word(Tag::T_BREAK, "break"));
        $this->reserve(new Word(Tag::T_AND, "and"));
        $this->reserve(new Word(Tag::T_OR, "or"));
        $this->reserve(new Word(Tag::T_XOR, "xor"));
        $this->reserve(new Word(Tag::T_TRY, "try"));
        $this->reserve(new Word(Tag::T_RESCUE, "rescue"));
        $this->reserve(new Word(Tag::T_FINALLY, "finally"));
        $this->reserve(new Word(Tag::T_RAISE, "raise"));
        $this->reserve(new Word(Tag::T_ELIF, "elif"));
        $this->reserve(new Word(Tag::T_ELSE, "else"));
        $this->reserve(new Word(Tag::T_CASE, "case"));
        $this->reserve(new Word(Tag::T_NOT, "not"));
        $this->reserve(new Word(Tag::T_FN, "fn"));
        $this->reserve(new Word(Tag::T_REQUIRE, "require"));
        $this->reserve(new Word(Tag::T_INCLUDE, "include"));
        $this->reserve(new Word(Tag::T_ONCE, "once"));
        $this->reserve(new Word(Tag::T_THEN, "then"));
        $this->reserve(new Word(Tag::T_BEGIN, "begin"));
        $this->reserve(new Word(Tag::T_END, "end"));
        $this->reserve(new Word(Tag::T_FROM, "from"));
        $this->reserve(new Word(Tag::T_TO, "to"));
        $this->reserve(new Word(Tag::T_BY, "by"));
        $this->reserve(new Word(Tag::T_WHEN, "when"));
        $this->reserve(new Word(Tag::T_UNLESS, "unless"));
        $this->reserve(new Word(Tag::T_MEMBER, "member"));
    }

    private function reserve(Word $t)
    {
        $this->keywords_hash[$t->getTag()] = $t->lexeme;
        $this->words[$t->lexeme] = $t;
    }

    protected function isEnd()
    {
        return $this->position >= $this->size;
    }

    public function rewind()
    {
        if ($this->size === 0) {
            exit;
        }

        $this->position = 0;
        $this->peek = $this->input[0];
    }

    public function consume($n = 1)
    {
        $this->position += $n;
        $this->peek = $this->isEnd()
            ? self::EOF
            : $this->input[$this->position];
    }

    public function preview($n = 1)
    {
        $next = $this->position + $n;

        return $next >= $this->size
            ? self::EOF
            : $this->input[$next];
    }

    public function previous()
    {
        $previous = $this->position - 1;
        return $previous < 0
            ? null
            : $this->input[$previous];
    }

    public function matches($string)
    {
        $len = strlen($string);

        for ($i = 0; $i < $len; $i++) {
            if ($this->preview($i) !== $string[$i]) {
                return false;
            }
        }

        return true;
    }

    protected function getWord($word)
    {
        return isset($this->words[$word])
            ? $this->words[$word]
            : null;
    }

    public function is($symbol)
    {
        return $this->peek === $symbol;
    }

    public function isNot($symbol)
    {
        return $this->peek !== $symbol;
    }

    abstract public function nextToken();
}
