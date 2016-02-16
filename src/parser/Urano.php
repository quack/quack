<?php

require_once '../toolkit/UranoToolkit.php';

use \UranoCompiler\Lexer\Tag;
use \UranoCompiler\Lexer\Tokenizer;
use \UranoCompiler\Parser\SyntaxError;
use \UranoCompiler\Parser\TokenReader;

$lexer = new Tokenizer(<<<SRC

  -+*10 + 1;
SRC
);

$parser = new TokenReader($lexer);

try {
  $parser->parse();
  $parser->dumpAst();
} catch (SyntaxError $e) {
  echo $e;
}

echo PHP_EOL;
