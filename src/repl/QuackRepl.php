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
require_once '/quack/quack/src/toolkit/QuackToolkit.php';

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
  echo "\033[01;36m";
  readline_callback_handler_install("Quack> ", 'readline_callback');
  echo "\033[0m";
}

function print_entire_license()
{
  echo file_get_contents(__DIR__ . "/../../LICENSE.md");
}

function readline_callback($command)
{
  $command = trim($command);

  if ($command === ':quit' || $command === ':q') {
    exit;
  } else if ($command === 'show c') {
    print_entire_license();
    exit;
  } else if ($command === '') {
    goto next;
  } else if ($command === ':clear') {
    $clear = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'cls' : 'clear';
    system($clear);
    goto next;
  }

  $lexer = new Tokenizer($command);
  $parser = new TokenReader($lexer);

  try {
    $parser->parse();
    /* when */ args_have('-a', '--ast') && $parser->dumpAst();
    /* when */ args_have('-p', '--python') && $parser->python($parser);
  } catch (SyntaxError $e) {
    echo $e;
  }

  next:
  readline_add_history($command);
  install_stream_handler();
}

function repl()
{
  echo "Type ^C or :quit to leave", PHP_EOL;
  install_stream_handler();

  while (true) {
    $write = NULL;
    $except = NULL;
    $stream = stream_select($read = [STDIN], $write, $except, NULL);

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
