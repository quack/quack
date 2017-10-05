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
            'column' => 0,
            'history' => [],
            'history_index' => 0
        ];
    }

    private function resetState()
    {
        $this->state['line'] = [];
        $this->state['column'] = 0;
    }

    private function getEvent($char_code)
    {
        $events = [
            // Backspace
            0x7F => function ($input) {
                $line = $this->state['line'];
                $column = $this->state['column'];
                if (0 === $column) {
                    return;
                }

                array_splice($line, $column - 1, 1);
                $this->state['line'] = $line;
                $this->state['column'] = $column- 1;
                $this->render();
            },
            // Arrow indicator
            0x5B => function ($input) {
                $next = ord($this->console->getChar());
                $column = $this->state['column'];
                $line = $this->state['line'];
                $arrow_events = [
                    0x43 => min(sizeof($line), $column + 1),
                    0x44 => max(0, $column - 1)
                ];

                if (isset($arrow_events[$next])) {
                    $this->state['column'] = $arrow_events[$next];
                }

                // TODO: Implement history, DELETE and colors

                $this->render();
            }
        ];

        return isset($events[$char_code])
            ? $events[$char_code]
            : null;
    }

    private function handleEnter()
    {
        $line = implode('', $this->state['line']);

        // Go the start of the line and set the command as done
        $this->state['history'][] = $line;
        $this->console->resetCursor();
        $this->renderPrompt(Console::FG_CYAN);
        $this->console->writeln('');
    }

    private function handleKeyPress($input)
    {
        if (ctype_cntrl($input)) {
            return;
        }

        $column = $this->state['column'];
        $line = $this->state['line'];
        $next_buffer = [$input];
        // Insert the new char in the column in the line buffer
        array_splice($line, $column, 0, $next_buffer);

        $this->state['line'] = $line;
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
        $this->handleEnter();

        $this->console->sttyRestoreCheckpoint();
        return implode('', $this->state['line']);
    }

    private function renderPrompt($color = Console::FG_YELLOW)
    {
        $this->console->setColor($color);
        $this->console->write('Quack> ');
        $this->console->resetColor();
    }

    private function renderLeftScroll()
    {
        $this->console->setColor(Console::BG_WHITE);
        $this->console->setColor(Console::FG_BLACK);
        $this->console->write(' < ');
        $this->console->setColor(Console::FG_CYAN);
        $this->console->write(' ... ');
        $this->console->resetColor();
    }

    private function render()
    {
        $line = implode('', $this->state['line']);
        $column = $this->state['column'];

        $this->console->clearLine();
        $this->console->resetCursor();

        $workspace = $this->console->getWidth() - 7;
        $this->renderPrompt();
        $show_left_scroll = $column >= $workspace;
        $text_size = $workspace;

        if ($show_left_scroll) {
            $this->renderLeftScroll();
            $text_size -= 9;
        }

        $from = $column - $text_size;
        $cursor = 7 + ($workspace - ($text_size - $column));
        $this->console->write(substr($line, max(0, $from), $text_size));
        $this->console->resetCursor();
        $this->console->forwardCursor($cursor);
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
