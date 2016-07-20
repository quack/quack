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
define('BASE_PATH', __DIR__ . '/..');
require_once(BASE_PATH . '/toolkit/QuackToolkit.php');

use \QuackCompiler\Lexer\Tokenizer;
use \QuackCompiler\Parser\SyntaxError;
use \QuackCompiler\Parser\TokenReader;

function start_repl()
{
    echo <<<LICENSE
Quack · Copyright (C) 2016 Marcelo Camargo
This program comes with ABSOLUTELY NO WARRANTY.
This is free software, and you are welcome to redistribute it
under certain conditions; type 'show c' for details.\n
LICENSE
    ;
    echo "Use quack --help for more information", PHP_EOL;

    if (args_have('-h', '--help')) {
        open_repl_help();
        return;
    }

    repl();
}

function install_stream_handler()
{
    begin_yellow();
    readline_callback_handler_install("Quack> ", 'readline_callback');
    end_yellow();
}

function begin_yellow()
{
    echo "\033[01;33m";
}

function end_yellow()
{
    echo "\033[0m";
}

function print_entire_license()
{
    echo file_get_contents(__DIR__ . "/../../LICENSE.md");
}

function readline_callback($command)
{
    $command = trim($command);

    switch (trim($command)) {
        case ':quit':
        case ':q':
            exit;
        case 'show c':
            print_entire_license();
            goto next;
        case '':
            goto next;
        case ':clear':
            $clear = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'cls' : 'clear';
            system($clear);
            goto next;
    }

    $lexer = new Tokenizer($command);
    $parser = new TokenReader($lexer);

    try {
        $parser->parse();
        # /* when */ args_have('-a', '--ast') && $parser->dumpAst();
        /* when */ args_have('-f', '--format') && $parser->format();
    } catch (SyntaxError $e) {
        echo $e;
    }

    next:
    readline_add_history($command);
    install_stream_handler();
}

function repl()
{
    $title = "Quack interactive mode";
    fwrite(STDOUT, "\x1b]2;{$title}\x07");

    echo "Type ^C or :quit to leave", PHP_EOL;
    install_stream_handler();

    while (true) {
        $write = null;
        $except = null;
        $stream = @stream_select($read = [STDIN], $write, $except, null);

        if ($stream && in_array(STDIN, $read)) {
            readline_callback_read_char();
        }
    }
}

function open_repl_help()
{
    // TODO
}

function args_have()
{
    global $argv;
    return count(array_intersect($argv, func_get_args())) > 0;
}

start_repl();
