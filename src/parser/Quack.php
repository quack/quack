<?php

require_once '../toolkit/QuackToolkit.php';

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Tokenizer;
use \QuackCompiler\Parser\SyntaxError;
use \QuackCompiler\Parser\TokenReader;

// $lexer = new Tokenizer(<<<SRC
//   module Test
// SRC
// );

$lexer = new Tokenizer(file_get_contents('../../bootstrap/parser/Parser.qk'));

$parser = new TokenReader($lexer);

try {
  $parser->parse();
  $parser->dumpAst();
} catch (SyntaxError $e) {
  echo $e;
}

echo PHP_EOL;
