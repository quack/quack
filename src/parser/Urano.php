<?php

require_once '../lexer/Lexer.php';
require_once '../lexer/Tag.php';
require_once '../lexer/Token.php';
require_once '../lexer/Word.php';
require_once '../lexer/Tokenizer.php';
require_once '../lexer/SymbolTable.php';
require_once '../lexer/SymbolDecypher.php';

require_once '../ast/Node.php';

require_once '../ast/Stmt.php';

require_once '../ast/BlockStmt.php';
require_once '../ast/BreakStmt.php';
require_once '../ast/ContinueStmt.php';
require_once '../ast/Expr.php';
require_once '../ast/ForeachStmt.php';
require_once '../ast/FunctionDecl.php';
require_once '../ast/GlobalStmt.php';
require_once '../ast/GotoStmt.php';
require_once '../ast/IfStmt.php';
require_once '../ast/LabelStmt.php';
require_once '../ast/ModuleStmt.php';
require_once '../ast/OpenStmt.php';
require_once '../ast/PrintStmt.php';
require_once '../ast/RaiseStmt.php';
require_once '../ast/ReturnStmt.php';
require_once '../ast/WhileStmt.php';

require_once '../parser/Parser.php';
require_once '../parser/SyntaxError.php';
require_once '../parser/TokenReader.php';

use \UranoCompiler\Lexer\Tokenizer;
use \UranoCompiler\Parser\SyntaxError;
use \UranoCompiler\Parser\TokenReader;


$lexer = new Tokenizer(<<<SRC

  1 + 2 * 3;


SRC
);
$parser = new TokenReader($lexer);

try {
  $parser->parse();
  $parser->dumpAst();
  // $query = "google-chrome \"http://mshang.ca/syntree/?i=" . $parser->guiAst() . "\"";
  //`$query`;
} catch (SyntaxError $e) {
  echo $e;
}

echo PHP_EOL;
