<?php

require_once '../toolkit/UranoToolkit.php';

use \UranoCompiler\Lexer\Tag;
use \UranoCompiler\Lexer\Tokenizer;
use \UranoCompiler\Parser\SyntaxError;
use \UranoCompiler\Parser\TokenReader;

$lexer = new Tokenizer(<<<SRC
:- label [] [ print 1 print 0xFF 12; <<< not 12] break 10 break break 10

continue 12 continue 0xFFFFFF

def name! 1;

def *   name [...
*ref;*symbol;    test] [
  print 12
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
