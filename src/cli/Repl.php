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

class Repl
{
    private $console;
    private $state;

    public function __construct(Console $console)
    {
        $this->console = $console;
        $this->state = [
            'line' => [],
            'column' => 0
        ];
    }

    private function resetState()
    {
        $this->state = [
            'line' => [],
            'column' => 0
        ];
    }

    private function getEvent($char_code)
    {
        $events = [
            0x7F => function ($input) {
                $line = $this->state['line'];
                $column = $this->state['column'];
                if (0 === $column) {
                    return;
                }

                array_splice($line, $column - 1, 1);
                $this->state['line'] = $line;
                $this->state['column'] = $this->state['column'] - 1;
                $this->render();
            }
        ];

        return isset($events[$char_code])
            ? $events[$char_code]
            : null;
    }

    private function handleKeyPress($input)
    {
        if (ctype_cntrl($input)) {
            return;
        }

        $this->state['line'] = array_merge($this->state['line'], [$input]);
        $this->state['column'] = $this->state['column'] + strlen($input);
        $this->render();
    }

    public function welcome()
    {
        $prelude = [
            'Quack - Copyright (C) 2016 Marcelo Camargo',
            'This program comes with ABSOLUTELY NO WARRANTY.',
            'This is free software, and you are welcome to redistribute it',
            'under certain conditions; type \'show c\' for details.',
            'Use quack --help for more information',
            'Type ^C or :quit to leave'
        ];

        $this->console->setTitle('Quack interactive mode');
        foreach ($prelude as $line) {
            $this->console->writeln($line);
        }
    }


    public function read()
    {
        $this->console->sttySaveCheckpoint();
        $this->console->sttyEnableCharEvents();

        do {
            $char = $this->console->getChar();
            $event = $this->getEvent(ord($char));
            if (null !== $event) {
                call_user_func_array($event, [$char]);
                continue;
            }

            $this->handleKeyPress($char);
        } while (ord($char) !== 10);

        $this->console->sttyRestoreCheckpoint();
        return implode('', $this->state['line']);
    }

    public function renderPrompt()
    {
        $this->console->setColor(Console::FG_YELLOW);
        $this->console->write('Quack> ');
        $this->console->resetColor();
    }

    private function render()
    {
        $line = implode('', $this->state['line']);
        $column = $this->state['column'];

        $this->console->clearLine();
        $this->console->resetCursor();

        $console_columns = $this->console->getWidth();
        if (strlen($line) > $console_columns - 7) {
            $this->renderPrompt();
            $this->console->setColor(Console::BG_WHITE);
            $this->console->setColor(Console::FG_BLACK);
            $this->console->write(' < ');
            $this->console->resetColor();
            $this->console->write(' ');
            $this->console->setColor(Console::FG_CYAN);
            $this->console->write('...');
            $this->console->resetColor();
            $this->console->write(' ');

            $available_size = $console_columns - 15;
            $this->console->write(substr($line, $column - $available_size, $available_size));
        } else {
            $this->renderPrompt();
            $this->console->write($line);
        }
    }

    public function loop()
    {
        $this->render();
        $line = $this->read();
        $this->resetState();
        $this->loop();
    }
}

require 'Console.php';

$repl = new Repl(new Console(STDIN, STDOUT, STDERR));
$repl->welcome();
$repl->loop();
