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

    public function __construct(Console $console)
    {
        $this->console = $console;
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

    public function renderPrompt()
    {
        $this->console->setFontColor(Console::YELLOW);
        $this->console->write('Quack> ');
        $this->console->resetFontColor();
    }

    public function loop()
    {
        $this->renderPrompt();
        $line = $this->console->read();

        if (null === $line) {
            $this->loop();
        }

        var_dump($line);
    }
}

require 'Console.php';

$repl = new Repl(new Console(STDIN, STDOUT, STDERR));
$repl->welcome();
$repl->loop();
