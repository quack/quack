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

use \Exception;
use \QuackCompiler\Lexer\Tokenizer;
use \QuackCompiler\Parser\EOFError;
use \QuackCompiler\Parser\TokenReader;
use \QuackCompiler\Scope\Scope;

class Repl extends Component
{
    private $console;

    public function __construct(Console $console)
    {
        $this->console = $console;
        parent::__construct([
            'line'          => [],
            'column'        => 0,
            'history'       => [],
            'history_index' => 0,
            'scope'         => new Scope(),
            'ast'           => null,
            'complete'      => true,
            'command'       => ''
        ]);
        $this->console = $console;
    }

    private function resetState()
    {
        $this->setState([
            'line'          => [],
            'column'        => 0,
            'history_index' => 0
        ]);
    }

    private function tick($char)
    {
        $event = $this->console->getEvent($char);
        if (null === $event) {
            $this->handleKeyPress($char);
            return;
        }

        if (is_string($event)) {
            return call_user_func([$this, $event]);
        }
    }

    private function handleHome()
    {
        $this->setState(['column' => 0]);
    }

    private function handleEnd()
    {
        $this->setState(['column' => sizeof($this->state('line'))]);
    }

    private function handleDelete()
    {
        list ($line, $column) = $this->state('line', 'column');
        if ($column === sizeof($line)) {
            return;
        }

        array_splice($line, $column, 1);
        $this->setState(['line' => $line, 'column' => $column]);
    }

    private function handleLeftArrow()
    {
        $column = $this->state('column');
        $this->setState(['column' => max(0, $column - 1)]);
    }

    private function handleRightArrow()
    {
        list ($line, $column) = $this->state('line', 'column');
        $this->setState(['column' => min(sizeof($line), $column + 1)]);
    }

    private function handleCtrlLeftArrow()
    {
        // TODO: Implement word group navigation
    }

    private function handleCtrlRightArrow()
    {
        // TODO: Implemet word group navigation
    }

    private function handleUpArrow()
    {
        list ($history, $index) = $this->state('history', 'history_index');
        $line = @$history[sizeof($history) - ($index + 1)];
        if (null !== $line) {
            $this->setState([
                'line'          => str_split($line),
                'history_index' => $index + 1,
                'column'        => strlen($line)
            ]);
        }
    }

    private function handleDownArrow()
    {
        list ($history, $index) = $this->state('history', 'history_index');
        $line = @$history[sizeof($history) - ($index - 1)];
        if (null !== $line) {
            $this->setState([
                'line'          => str_split($line),
                'history_index' => $index - 1,
                'column'        => strlen($line)
            ]);
        } elseif ($index === 1) {
            $this->resetState();
        }
    }

    private function handleBackspace()
    {
        list ($line, $column) = $this->state('line', 'column');

        if (0 === $column) {
            return;
        }

        array_splice($line, $column - 1, 1);
        $this->setState([
            'line'   => $line,
            'column' => $column - 1
        ]);
    }

    private function handleClearScreen()
    {
        $this->console->clear();
        $this->console->moveCursorToHome();
        $this->setState([]);
    }

    private function handleEnter()
    {
        $line = trim(implode('', $this->state('line')));
        // Push line to the history
        if ($line !== '') {
            $this->setState([
                'history' => array_merge($this->state('history'), [$line])
            ]);
        }

        // Go to the start of line and set the command as done
        $this->console->resetCursor();
        $this->renderPrompt(Console::FG_CYAN);
        $this->console->writeln('');
    }

    private function handleKeyPress($input)
    {
        if (ctype_cntrl($input)) {
            return;
        }

        list ($line, $column) = $this->state('line', 'column');
        $next_buffer = [$input];
        // Insert the new char in the column in the line buffer
        array_splice($line, $column, 0, $next_buffer);

        $this->setState([
            'line'   => $line,
            'column' => $this->state('column') + strlen($input)
        ]);
    }

    public function handleQuit()
    {
        $this->console->setColor(Console::FG_BLUE);
        $this->console->writeln(' > So long, and thanks for all the fish!');
        $this->console->resetColor();
        exit;
    }

    private function intercept($command)
    {
        switch ($command) {
            case ':clear':
                return $this->handleClearScreen();
            case ':quit':
                return $this->handleQuit();
        }
    }

    public function welcome()
    {
        $prelude = [
            'Quack - Copyright (C) 2017 Marcelo Camargo',
            'This program comes with ABSOLUTELY NO WARRANTY.',
            'This is free software, and you are welcome to redistribute it',
            'under certain conditions.',
            'Use quack --help for more information',
            'Type ^C or :quit to leave'
        ];

        $this->console->setTitle('Quack interactive mode');
        foreach ($prelude as $line) {
            $this->console->writeln($line);
        }
    }

    public function handleRead()
    {
        $this->console->sttySaveCheckpoint();
        $this->console->sttyEnableCharEvents();

        do {
            $char = $this->console->getChar();
            $this->tick($char);
        } while (ord($char) !== 10);

        $this->handleEnter();
        $this->console->sttyRestoreCheckpoint();
    }

    private function renderPrompt($color = Console::FG_YELLOW)
    {
        $prompt = $this->state('complete') ? 'Quack> ' : '.....> ';
        $prompt_color = $this->state('complete') ? $color : Console::FG_BOLD_GREEN;
        $this->console->setColor($prompt_color);
        $this->console->write($prompt);
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

    public function render()
    {
        $line = implode('', $this->state('line'));
        $column = $this->state('column');

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
        $limit = $from <= 0 ? 1 : 0;
        // TODO: We can give color after embedding the tokenizer here
        $colored_line = substr($line, max(0, $from), $text_size - $limit);

        $this->console->write($colored_line);
        $this->console->resetCursor();
        $this->console->forwardCursor($cursor);
    }

    private function compile($source)
    {
        if ('' === $source) {
            return;
        }

        $command = $this->state('complete')
            ? $source
            : $this->state('command') . ' ' . $source;

        $lexer = new Tokenizer($command);
        $parser = new TokenReader($lexer);

        try {
            $parser->parse();
            if (null === $this->state('ast')) {
                $parser->ast->injectScope($this->state('scope'));
                $parser->ast->runTypeChecker();
                // Save AST in case of success
                $this->console->write($parser->beautify());
                $this->setState(['ast' => $parser->ast, 'complete' => true]);
            } else {
                $this->console->write($parser->beautify());
                $this->state('ast')->attachValidAST($parser->ast);
                $this->setState(['complete' => true]);
            }
        } catch (EOFError $error) {
            // If EOF, user didn't finish the statement
            $this->setState(['complete' => false, 'command' => $command]);
        } catch (Exception $error) {
            $this->console->write($error);
            $this->setState(['complete' => true, 'command' => '']);
        }
    }

    public function start()
    {
        $this->render();
        while (true) {
            $this->handleRead();
            $line = trim(implode('', $this->state('line')));

            if (':' === substr($line, 0, 1)) {
                $this->intercept($line);
            } else {
                $this->compile($line);
            }

            $this->resetState();
        }
    }
}
