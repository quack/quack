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
namespace QuackCompiler\Types;

use \Exception;

class TypeError extends Exception
{
    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function __toString()
    {
        return join([
            '----- ', BEGIN_BLUE, BEGIN_BOLD, 'TYPE MISMATCH', END_BLUE, ' ',
            str_repeat('-', 80), PHP_EOL,
            BEGIN_RED,
            '**** Oops! I\'ve found a ',
            BEGIN_GREEN, 'type error', END_GREEN,
            BEGIN_RED, '!', PHP_EOL,
            '     ', $this->message, END_RED, PHP_EOL,
            str_repeat('-', 100), PHP_EOL
        ]);
    }
}
