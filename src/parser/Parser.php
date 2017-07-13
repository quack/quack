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
use \QuackCompiler\Lexer\Tokenizer;
use \QuackCompiler\Parselets\Parselet;

abstract class Parser
{
    use Parselet;

    public $input;
    public $lookahead;
    public $scope_level = 0;

    public function __construct(Tokenizer $input)
    {
        $this->input = $input;
        $this->consume();
    }

    public function match($tag)
    {
        $hint = null;

        if ($this->lookahead->getTag() === $tag) {
            return $this->consume();
        }

        // When, after an error, the programmer provided an identifier,
        // we'll calculate the levenshtein distance between the expected lexeme
        // and the provided lexeme and give a hint about
        if (Tag::T_IDENT === $this->lookahead->getTag() && array_key_exists($tag, $this->input->keywords_hash)) {
            $expected_lexeme = $this->input->keywords_hash[$tag];
            $provided_lexeme = $this->resolveScope($this->lookahead->getPointer());

            $distance = levenshtein($expected_lexeme, $provided_lexeme);

            if ($distance <= 2) {
                $hint = "Did you mean \"{$expected_lexeme}\" instead of \"{$provided_lexeme}\"?";
            }
        }

        $params = [
            'expected' => $tag,
            'found'    => $this->lookahead,
            'parser'   => $this,
            'hint'     => $hint
        ];

        if (0 === $this->lookahead->getTag()) {
            throw new EOFError($params);
        };

        throw new SyntaxError($params);
    }

    public function opt($tag)
    {
        if ($this->lookahead->getTag() === $tag) {
            $pointer = $this->consume();
            return $pointer === null ? true : $pointer;
        }
        return false;
    }

    public function is($tag)
    {
        return $this->lookahead->getTag() === $tag;
    }

    public function consume()
    {
        $pointer = $this->lookahead === null ?: $this->lookahead->getPointer();
        $this->lookahead = $this->input->nextToken();
        return $pointer;
    }

    public function consumeIf($symbol)
    {
        if ($this->is($symbol)) {
            $this->consume();
            return true;
        }

        return false;
    }

    public function consumeAndFetch()
    {
        $clone = $this->lookahead;
        $this->lookahead = $this->input->nextToken();
        return $clone;
    }

    public function resolveScope($pointer)
    {
        return $this->input->getSymbolTable()->get($pointer);
    }

    public function position()
    {
        return ["line" => $this->input->line, "column" => $this->input->column];
    }

    public function openScope()
    {
        $this->scope_level++;
    }

    public function closeScope()
    {
        $this->scope_level--;
    }

    public function indent()
    {
        return str_repeat('  ', $this->scope_level);
    }

    public function dedent()
    {
        return str_repeat('  ', max(0, $this->scope_level - 1));
    }
}
