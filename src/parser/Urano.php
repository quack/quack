<?php

require_once '../lexer/Lexer.php';
require_once '../lexer/Tag.php';
require_once '../lexer/Token.php';
require_once '../lexer/Word.php';
require_once '../lexer/Tokenizer.php';
require_once '../lexer/SymbolTable.php';
require_once '../lexer/SymbolDecypher.php';

require_once '../ast/Node.php';
require_once '../ast/FunctionDecl.php';
require_once '../ast/ModuleStmt.php';
require_once '../ast/OpenStmt.php';
require_once '../ast/PrintStmt.php';
require_once '../ast/Expr.php';

require_once '../parser/Parser.php';
require_once '../parser/SyntaxError.php';
require_once '../parser/TokenReader.php';

use \UranoCompiler\Lexer\Tokenizer;
use \UranoCompiler\Parser\SyntaxError;
use \UranoCompiler\Parser\TokenReader;


$lexer = new Tokenizer(<<<SRC
  module enterprise.user

  open enterprise.finances
  open enterprise.human_resources as hr
SRC
);
$parser = new TokenReader($lexer);

try {
  $parser->parse();
  $query = "google-chrome \"http://mshang.ca/syntree/?i=" . $parser->guiAst() . "\"";
  `$query`;
} catch (SyntaxError $e) {
  echo $e;
}

echo PHP_EOL;
