<?php

require_once '../toolkit/QuackToolkit.php';

use \QuackCompiler\Lexer\Tokenizer;
use \QuackCompiler\Parser\SyntaxError;
use \QuackCompiler\Parser\TokenReader;

function start_repl()
{
  echo "Quack 0.1 · Use quack --help for more information", PHP_EOL;

  if (args_have('-h', '--help')) {
    open_repl_help();
    return;
  }

  repl();
}

function repl()
{
  echo "Type ^C or :quit to leave", PHP_EOL;

  while (true) {
    echo  "\033[01;36mQuack> \033[0m";
    $command = readline();

    if ($command === ':quit') {
      exit;
    } else if (trim($command) === "") {
      continue;
    }

    $lexer = new Tokenizer($command);
    $parser = new TokenReader($lexer);

    try {
      $parser->parse();
      if (args_have('-a', '--ast')) {
        $parser->dumpAst();
      }

      if (args_have('-p', '--python')) {
        $parser->python($parser);
      }
    } catch (SyntaxError $e) {
      echo $e;
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
