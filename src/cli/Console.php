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
namespace QuackCompiler\Cli;

class Console
{
    private $stdin;
    private $stdout;
    private $stderr;
    private $stty_settings;
    private $columns;
    private $event_tree;

    const FG_WHITE      = '1;37';
    const FG_YELLOW     = '1;33';
    const FG_RED        = '0;31';
    const FG_BLACK      = '1;30';
    const FG_CYAN       = '0;36';
    const FG_BOLD_GREEN = '1;32';
    const FG_MAGENTA    = '0;35';
    const FG_GREEN      = '0;32';
    const FG_BLUE       = '0;34';
    const BG_WHITE      = '47';
    const BG_GREEN      = '42';
    const BG_RED        = '41';
    const BG_BLUE       = '44';
    const BOLD          = '1';

    public function __construct($stdin, $stdout, $stderr)
    {
        $this->stdin = $stdin;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }

    public function subscribe($event_tree)
    {
        $this->event_tree = $event_tree;
    }

    public function write($buffer)
    {
        return fwrite($this->stdout, $buffer);
    }

    public function getChar()
    {
        return fgetc($this->stdin);
    }

    public function getWidth()
    {
        if (null === $this->columns) {
            $this->columns = (int) `tput cols`;
        }

        return $this->columns;
    }

    public function stty($options)
    {
        $output = [];
        exec("stty $options", $output);
        return implode(' ', $output);
    }

    public function sttySaveCheckpoint()
    {
        return $this->stty_settings = preg_replace('#.*; ?#s', '', $this->stty('-a'));
    }

    public function sttyRestoreCheckpoint()
    {
        return $this->stty($this->stty_settings);
    }

    public function sttyEnableCharEvents()
    {
        return $this->stty('cbreak -echo');
    }

    public function clearLine()
    {
        return $this->write(sprintf("%c[2K", 0x1B));
    }

    public function clear()
    {
        return $this->write(sprintf("%c[2J", 0x1B));
    }

    public function resetCursor()
    {
        return $this->write(sprintf("%c[%dD", 0x1B, 0xFFFF));
    }

    public function forwardCursor($n)
    {
        return $this->write(sprintf("%c[%dC", 0x1B, $n));
    }

    public function backwardCursor($n)
    {
        return $this->write(sprintf("%c[%dD", 0x1B, $n));
    }

    public function moveCursorToHome()
    {
        return $this->write(sprintf("%c[H", 0x1B));
    }

    public function writeln($buffer)
    {
        return $this->write($buffer . PHP_EOL);
    }

    public function setTitle($title)
    {
        return $this->write(sprintf("%c]2;%s%c", 0x1B, $title, 0x7));
    }

    public function setColor($color)
    {
        return $this->write(sprintf("%c[%sm", 0x1B, $color));
    }

    public function resetColor()
    {
        return $this->write(sprintf("%c[0m", 0x1B));
    }

    public function scolor($color, $text)
    {
        return sprintf('%c[%sm%s%c[0m', 0x1B, $color, $text, 0x1B);
    }

    public function getEvent($char)
    {
        $event = @$this->event_tree[ord($char)];
        if (null === $event) {
            return null;
        }

        while (!is_string($event)) {
            $next = ord($this->getChar());
            if (isset($event[$next])) {
                $event = $event[$next];
            } else break;
        }

        return $event;
    }
}
