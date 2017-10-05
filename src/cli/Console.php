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
    private $stty_settings;
    private $columns;

    const FG_YELLOW = '1;33';
    const FG_BLACK = '1;30';
    const FG_CYAN = '0;36';

    const BG_WHITE = '47';

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
        $this->stty_settings = preg_replace('#.*; ?#s', '', $this->stty('--all'));
    }

    public function sttyRestoreCheckpoint()
    {
        $this->stty($this->stty_settings);
    }

    public function sttyEnableCharEvents()
    {
        $this->stty('cbreak -echo');
    }

    public function clearLine()
    {
        $this->write(sprintf("%c[2K", 0x1B));
    }

    public function resetCursor()
    {
        $this->write(sprintf("%c[%dD", 0x1B, 0xFFFF));
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

    public function setColor($color)
    {
        return $this->write("\033[{$color}m");
    }

    public function resetColor()
    {
        $this->write("\033[0m");
    }

    private function isWindows()
    {
        return 'WIN' === strtoupper(substr(PHP_OS, 0, 3));
    }
}

