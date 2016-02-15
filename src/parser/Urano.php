<?php

require_once '../toolkit/UranoToolkit.php';

use \UranoCompiler\Lexer\Tag;
use \UranoCompiler\Lexer\Tokenizer;
use \UranoCompiler\Parser\SyntaxError;
use \UranoCompiler\Parser\TokenReader;

$lexer = new Tokenizer(<<<SRC
  while 1 [
    while 2 [
      while 3 [
        print 4
      ]
    ]
  ]
SRC
);

$parser = new TokenReader($lexer);

try {
  $parser->parse();
  $parser->format();
} catch (SyntaxError $e) {
  echo $e;
}

echo PHP_EOL;
