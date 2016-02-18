<?php

require_once '../toolkit/UranoToolkit.php';

use \UranoCompiler\Lexer\Tag;
use \UranoCompiler\Lexer\Tokenizer;
use \UranoCompiler\Parser\SyntaxError;
use \UranoCompiler\Parser\TokenReader;

$lexer = new Tokenizer(<<<SRC
  while 1 [
    <<< 1! and 2 and 3 or 4 + 2 and (1 ? 2 : 3 ? 4 : 5)
  ]


SRC
);

$parser = new TokenReader($lexer);

try {
  $parser->parse();
  $parser->python();
} catch (SyntaxError $e) {
  echo $e;
}

echo PHP_EOL;
