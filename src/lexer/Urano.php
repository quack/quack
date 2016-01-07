<?php

require_once 'Lexer.php';
require_once 'Tag.php';
require_once 'Token.php';
require_once 'Word.php';
require_once 'Tokenizer.php';
require_once 'SymbolTable.php';
require_once 'SymbolDecypher.php';


use \UranoCompiler\Lexer\Tokenizer;
use \UranoCompiler\Lexer\Tag;

$lexer = new Tokenizer("let add = &(+)");

$lexer->printTokens();

// Eager evaluation
/*
foreach ($lexer->eagerlyEvaluate(true) as $token) {
  echo $token;
}
*/

echo PHP_EOL;

// Lazy, generator based, evaluation
/*
try {
  $lexer->rewind();
  $symbol_table = $lexer->getSymbolTable();

  $token = $lexer->nextToken();
  $token->showSymbolTable($symbol_table);

  while ($token->getTag() !== Tokenizer::EOF_TYPE) {
    echo $token;
    $token = $lexer->nextToken();
    $token->showSymbolTable($symbol_table);
  }

  echo PHP_EOL;

} catch (Exception $e) {
  echo $e->getMessage(), PHP_EOL;
}
*/
