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

            if ((ctype_alpha($this->peek) || $this->is('_'))
                ||
                ($this->is('_') && ctype_alnum((string) $this->preview()))
                ) {
                    return $this->identifier();
            }

            if (ctype_space($this->peek)) {
                $this->space();
                continue;
            }

            if ($this->matches(':') && (ctype_alpha($this->preview()) || '_' === $this->preview())) {
                return $this->atom();
            }

            if ($this->matches('(*') && $this->previous() !== '&') {
                $this->comment();
                continue;
            }

            if ($this->is('"') || $this->is("'")) {
                return $this->string($this->peek);
            }

            if ($this->is('/')) {
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
        $is_double = false;

        // Hexadecimal or binary
        if ($this->peek === '0' && (in_array(strtolower($this->preview()), ['x', 'b']))) {
            $buffer[] = $this->readChar(); // 0

            // b and x are identifiers if there is no valid value after then
            if (($this->peek === 'b' && (int) $this->preview() >> 1)         // Fake bin
                || ($this->peek === 'x' && !ctype_xdigit($this->preview()))) { // Fake hex

                $value = '0';
                $this->column += 1;
                return new Token(Tag::T_INTEGER, $this->symbol_table->add($value));
            }

            $buffer[] = $b_or_x = strtolower($this->readChar()); // b or x

            switch ($b_or_x) {
                case 'b':
                    do {
                        $buffer[] = $this->readChar();
                    } while (ctype_digit($this->peek) && !((int) $this->peek >> 1));

                    break;

                case 'x':
                    do {
                        $buffer[] = $this->readChar();
                    } while (ctype_xdigit($this->peek));

                    break;
            }

            $value = implode($buffer);
            $this->column += sizeof($value);

            return new Token(Tag::T_INTEGER, $this->symbol_table->add($value));
        }

        // Is it decimal or octal?
        parse_int:
        do {
            $buffer[] = $this->readChar();
        } while (ctype_digit((string) $this->peek));

        if ($this->peek === '.' && ctype_digit((string) $this->preview()) && !$is_double) {
            $buffer[] = $this->readChar();
            $is_double = true;
            goto parse_int;
        }

        // Try to check for octal compatibility
        if (!$is_double && $buffer[0] === '0' && sizeof($buffer) > 1) {
            $oct_buffer = [];
            $current_buffer_size = sizeof($buffer);

            $index = 0;
            while ($index < $current_buffer_size && in_array($buffer[$index], range(0, 7))) {
                $oct_buffer[] = $buffer[$index];
                $index++;
            }

            $value = implode($oct_buffer);
            $this->column += $current_buffer_size;
            return new Token(Tag::T_INTEGER, $this->symbol_table->add($value));
        }

        // Decimal
        $size = sizeof($buffer);
        $value = implode($buffer);

        $this->column += $size;
        return new Token($is_double ? Tag::T_DOUBLE : Tag::T_INTEGER, $this->symbol_table->add($value));
    }

    private function identifier()
    {
        $buffer = [];

        do {
            $buffer[] = $this->readChar();
        } while (ctype_alnum((string) $this->peek) || $this->peek === '_');

        $string = implode($buffer);

        $word = $this->getWord($string);
        $this->column += sizeof($buffer);

        if ($word !== null) {
            return $word;
        }

        return new Token(Tag::T_IDENT, $this->symbol_table->add($string));
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

        return new Token(Tag::T_STRING, $this->symbol_table->add($string));
    }

    private function regex()
    {
        $buffer = [];
        $buffer[] = $this->readChar();
        $this->column++;

        while (!$this->isEnd() && !($this->is('/') && $this->previous() !== '\\')) {
            $buffer[] = $this->readChar();
            $this->column++;
        }

        if (!$this->isEnd()) {
            $buffer[] = $this->readChar();
            $this->column++;

            // Regex modifiers
            $allowed_modifiers = [
                'i', 'm', 's', 'x', 'e', 'A', 'D', 'S', 'U', 'X', 'J', 'u'
            ];

            while (in_array($this->peek, $allowed_modifiers, true)) {
                $buffer[] = $this->readChar();
                $this->column++;
            }
        }

        $regex = implode($buffer);

        return new Token(Tag::T_REGEX, $this->symbol_table->add($regex));
    }

    private function comment()
    {
        $new_line = array_map('ord', ["\r", "\n", "\r\n", PHP_EOL]);

        $this->consume(2);
        $this->column += 2;

        $buffer = [];
        while (!$this->isEnd() && !($this->is('*') && $this->preview() === ')')) {
            $chr = $this->readChar();
            $this->column++;

            if (in_array($chr, $new_line, true)) {
                $this->column = 1;
                $this->line++;
            }
        }

        $comment = implode($buffer);

        if (!$this->isEnd()) {
            $this->consume(2);
            $this->column += 2;
        }
    }

    private function atom()
    {
        $this->consume(); // :

        do {
            $buffer[] = $this->readChar();
        } while (ctype_alnum((string) $this->peek) || $this->peek === '_');

        $atom = implode($buffer);
        return new Token(Tag::T_ATOM, $this->symbol_table->add($atom));
    }

    private function readChar()
    {
        $char = $this->peek;
        $this->consume();
        return $char;
    }

    public function & getSymbolTable()
    {
        return $this->symbol_table;
    }

    public function eagerlyEvaluate($show_symbol_table = false)
    {
        $this->rewind();
        $symbol_table = &$this->getSymbolTable();
        $token_stream = [];

        while ($this->peek != self::EOF) {
            $token_stream[] = $this->nextToken();
            if ($show_symbol_table) {
                $token_stream[sizeof($token_stream) - 1]->showSymbolTable($symbol_table);
            }
        }

        return $token_stream;
    }

    public function printTokens()
    {
        $this->rewind();
        $symbol_table = &$this->getSymbolTable();

        $token = $this->nextToken();
        $token->showSymbolTable($symbol_table);

        while ($token->getTag() !== static::EOF_TYPE) {
            echo $token;
            $token = $this->nextToken();
            $token->showSymbolTable($symbol_table);
        }
    }
}
