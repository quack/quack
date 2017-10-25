<?php
/**
 * Quack Compiler and toolkit
 * Copyright (C) 2015-2017 Quack and CONTRIBUTORS
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

class Tokenizer extends Lexer
{
    public $line = 1;
    public $column = 0;

    public function __construct($input)
    {
        parent::__construct($input);
    }

    public function nextToken()
    {
        while ($this->peek != self::EOF) {
            if (ctype_digit($this->peek)) {
                return $this->digit();
            }

            if (ctype_upper($this->peek)) {
                return $this->identifier(Tag::T_TYPENAME);
            }

            if ((ctype_alpha($this->peek) || $this->is('_'))
                || ($this->is('_') && ctype_alnum((string) $this->preview()))) {
                    return $this->identifier();
            }

            if (ctype_space($this->peek)) {
                $this->space();
                continue;
            }

            if ($this->matches('@') && (ctype_alpha($this->preview()) || '_' === $this->preview())) {
                return $this->atom();
            }

            if ($this->matches('--') || $this->matches('#!')) {
                $this->singlelineComment();
                continue;
            }

            if ($this->matches('{-')) {
                $this->multilineComment();
                continue;
            }

            if ($this->is('"') || $this->is("'")) {
                return $this->string($this->peek);
            }

            if ($this->matches('&/')) {
                return $this->regex();
            }

            // Multichar symbol analysis
            return SymbolDecypher::{$this->peek}($this);
        }

        return new Token(self::EOF_TYPE);
    }

    public function digit()
    {
        $buffer = [];
        $number = $this->readChar();

        // check if the number is /0.{1}[0-9a-fA-F]/
        if (!$this->isEnd() && $number === '0' && ctype_xdigit($this->preview())) {
            $tag = Tag::T_INT_HEX;
            $found = false;
            if ($this->peek === 'x') { // we know that preview is hexadec
                $found = true;
                $buffer[] = $number;
                do {
                    $buffer[] = $this->readChar();
                } while (ctype_xdigit($this->peek));
            } else {
                $bits = 0;
                if (ctype_digit($this->preview())) { // check if is dec
                    if ($this->peek === 'b') {
                        $bits = 1;
                        $tag = Tag::T_INT_BIN;
                        $found = true;
                    } elseif ($this->peek === 'o') {
                        $bits = 3;
                        $tag = Tag::T_INT_OCT;
                        $found = true;
                    }

                    if ($found) {
                        $buffer[] = $number;
                        do {
                            $buffer[] = $this->readChar();
                        } while (ctype_digit($this->peek) &&
                            !((int) $this->peek >> $bits));

                        if (ctype_alpha(end($buffer))) {
                            $found = false; // false positive:0b[2-9] or 0o[8-9]
                            $buffer = []; // reset buffer
                            $this->stepback(); // retract
                        }
                    }
                }
            }

            if ($found) {
                $value = implode($buffer);
                $this->column += count($buffer);
                return new Token($tag, $value);
            }
        }

        $tag = Tag::T_INTEGER;
        $buffer[] = $number;
        // state 1: looking for a number
        $buffer = array_merge($buffer, $this->integer());
        // check optional floating state: looking for a '.' and integers
        if (!$this->isEnd() && $this->peek === '.'
            && ctype_digit($this->preview())) {
            $tag = Tag::T_DOUBLE;
            $buffer[] = $this->readChar(); // append '.'
            $buffer = array_merge($buffer, $this->integer());
        }
        // check optional exp state: looking for a 'e', 'e+' or 'e-' and integers
        if (!$this->isEnd() && $this->is('e')) {
            if (ctype_digit($this->preview())) {
                $tag = Tag::T_DOUBLE_EXP;
                $buffer[] = $this->readChar(); // append 'e'
                $buffer = array_merge($buffer, $this->integer());
            } else if (($this->preview() === '+' || $this->preview() === '-')
              && ctype_digit($this->preview(2))) {
                $tag = Tag::T_DOUBLE_EXP;
                $buffer[] = $this->readChar(); // append 'e'
                $buffer[] = $this->readChar(); // append '+' or '-'
                $buffer = array_merge($buffer, $this->integer());
            }
        }

        $value = implode($buffer);
        $this->column += count($buffer);
        return new Token($tag, $value);
    }

    private function integer()
    {
        $arr = [];
        while (!$this->isEnd() && ctype_digit($this->peek)) {
            $arr[] = $this->readChar();
        }

        return $arr;
    }

    private function identifier($token = Tag::T_IDENT)
    {
        $buffer = [];

        do {
            $buffer[] = $this->readChar();
        } while (ctype_alnum((string) $this->peek) || $this->peek === '_');

        $string = implode($buffer);

        $word = $this->getWord($string);
        $this->column += count($buffer);

        if ($word !== null) {
            return $word;
        }

        return new Token($token, $string);
    }

    private function space()
    {
        $new_line = array_map('ord', ["\r", "\n", "\r\n", PHP_EOL]);

        do {
            if (in_array(ord($this->peek), $new_line)) {
                $this->line++;
                $this->column = 1;
            } else {
                $this->column++;
            }

            $this->consume();
        } while (ctype_space($this->peek));
    }

    private function string($delimiter)
    {
        $this->consume();
        $this->column++;

        $buffer = [];
        while (!$this->isEnd() && !($this->is($delimiter) && $this->previous() !== '\\')) {
            $buffer[] = $this->readChar();
            $this->column++;
        }

        $string = implode($buffer);

        if (!$this->isEnd()) {
            $this->consume();
            $this->column++;
        }

        $token = new Token(Tag::T_STRING, $string);
        // Inject information about the delimiter for code formatting
        $token->metadata['delimiter'] = $delimiter;
        return $token;
    }

    private function regex()
    {
        $buffer = [];
        $buffer[] = $this->readChar();
        $buffer[] = $this->readChar();
        $this->column += 2;

        while (!$this->isEnd() && !($this->is('/') && $this->previous() !== '\\')) {
            $buffer[] = $this->readChar();
            $this->column++;
        }

        if (!$this->isEnd()) {
            $buffer[] = $this->readChar();
            $this->column++;
        }

        // Regex modifiers
        $allowed_modifiers = [
            'i', 'm', 's', 'x', 'e', 'A', 'D', 'S', 'U', 'X', 'J', 'u'
        ];

        while (!$this->isEnd()) {
            $char = $this->readChar();
            if (in_array($char, $allowed_modifiers, true)) {
                $buffer[] = $char;
                $this->column++;
            } else {
                $this->column--;
                $this->stepback();
                break;
            }
        }

        $regex = implode($buffer);
        return new Token(Tag::T_REGEX, $regex);
    }

    private function singleLineComment()
    {
        $newline = array_map('ord', ["\r", "\n", "\r\n", PHP_EOL]);
        $this->consume(2); // --

        while (!$this->isEnd()) {
            $code = ord($this->readChar());
            $this->column++;

            if (in_array($code, $newline, true)) {
                $this->line++;
                break;
            }
        }
    }

    private function multilineComment()
    {
        $newline = array_map('ord', ["\r", "\n", "\r\n", PHP_EOL]);
        $this->consume(2); // {-

        while (!$this->isEnd() && !$this->matches('-}')) {
            $code = ord($this->readChar());
            $this->column++;

            if (in_array($code, $newline, true)) {
                $this->line++;
            }
        }

        if (!$this->isEnd()) {
            $this->consume(2);
            $this->column += 2;
        }
    }

    private function atom()
    {
        do {
            $buffer[] = $this->readChar();
            $this->column++;
        } while (ctype_alnum((string) $this->peek) || $this->peek === '_');

        $atom = implode($buffer);
        return new Token(Tag::T_ATOM, $atom);
    }

    private function readChar()
    {
        $char = $this->peek;
        $this->consume();
        return $char;
    }

    public function eagerlyEvaluate()
    {
        $this->rewind();
        $token_stream = [];

        while ($this->peek != self::EOF) {
            $token_stream[] = $this->nextToken();
        }

        return $token_stream;
    }

    public function printTokens()
    {
        $this->rewind();
        $token = $this->nextToken();

        while ($token->getTag() !== static::EOF_TYPE) {
            echo $token;
            $token = $this->nextToken();
        }
    }
}
