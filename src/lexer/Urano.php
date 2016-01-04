<?php

require_once "Lexer.php";
require_once "Tag.php";
require_once "Token.php";
require_once "Word.php";
require_once "Tokenizer.php";
require_once "SymbolTable.php";
require_once "SymbolDecypher.php";

use \UranoCompiler\Lexer\Tokenizer;
use \UranoCompiler\Lexer\Tag;

try {
  $lexer = new Tokenizer("0 1 0xA 213 07681 0xF");
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
