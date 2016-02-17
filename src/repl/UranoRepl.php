<?php

require_once '../toolkit/UranoToolkit.php';

use \UranoCompiler\Lexer\Tokenizer;
use \UranoCompiler\Parser\SyntaxError;
use \UranoCompiler\Parser\TokenReader;

function start_repl()
{
  echo "Urano 0.1 · Use urano --help for more information", PHP_EOL;

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
    echo  "\033[01;36mUrano> \033[0m";
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
      echo $parser->format($parser);
      if (args_have('-a', '--ast')) {
        $parser->dumpAst();
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
