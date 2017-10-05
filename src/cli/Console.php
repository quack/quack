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
namespace QuackCompiler\Cli;

class Console
{
    private $stdin;
    private $stdout;
    private $stderr;

    const YELLOW = '1;33';

    public function __construct($stdin, $stdout, $stderr)
    {
        $this->stdin = $stdin;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }

    public function write($buffer)
    {
        return fwrite($this->stdout, $buffer);
    }

    private static function stty($options)
    {
        $output = [];
        exec("stty $options", $output);
        return implode(' ', $output);
    }

    public function read()
    {
        $stty_settings = preg_replace('#.*; ?#s', '', self::stty('--all'));
        self::stty('cbreak -echo');
        self::stty('echoe');

        $stack = [];
        $cursor = 0;
        do {
            $char = fgetc($this->stdin);
            $char_code = ord($char);

            if (27 === $char_code) {
                if (127 === ord($char)) {
                    if ($cursor > 0) {
                        // Decrement cursor on backspace
                        $cursor--;
                        // Pop from stack
                        array_pop($stack);
                        printf("%c[1D", 27); // Move cursor 1 char to the left
                        printf("%c[K", 27); // Clear garbage on right
                    }

                }
            } elseif (ctype_print($char)) {
                // Accumulate character to the stack
                $stack[] = $char;
                // Walk in tape
                $cursor++;
                $this->write($char);
            }
        } while (ord($char) !== 10);

        self::stty($stty_settings);
        var_dump($stack);
        exit;
    }

    public function writeln($buffer)
    {
        return $this->write($buffer . PHP_EOL);
    }

    public function setTitle($title)
    {
        if ($this->isWindows()) {
            return `title {$title}`;
        }

        return $this->write("\x1b]2;{$title}\x07");
    }

    public function setFontColor($color)
    {
        return $this->write("\033[{$color}m");
    }

    public function resetFontColor()
    {
        $this->write("\033[0m");
    }

    private function isWindows()
    {
        return 'WIN' === strtoupper(substr(PHP_OS, 0, 3));
    }
}

