<?php

require_once '../toolkit/UranoToolkit.php';

use \UranoCompiler\Lexer\Tag;
use \UranoCompiler\Lexer\Tokenizer;
use \UranoCompiler\Parser\SyntaxError;
use \UranoCompiler\Parser\TokenReader;

$lexer = new Tokenizer(<<<SRC
def user!1;def*user!1;def user[n][1;]def user[...*op][<<< 10]12;12;def add![]
def sub[n][while 1 [print 2 foreach x in 1 [print 1]
    if 1 print 2
    if 2 [ print 4 print 5 if 1 [<<<1] ]
    :- n :- m :- o
  module HaskellCamargo.Maybe open Data.List
  open Data.Monad as         M
  raise 1 raise 2
  ]
]
[
while 1 [
break
continue
]global x goto xy goto n
]

global name

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
