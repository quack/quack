<?php

require_once '../lexer/Lexer.php';
require_once '../lexer/Tag.php';
require_once '../lexer/Token.php';
require_once '../lexer/Word.php';
require_once '../lexer/Tokenizer.php';
require_once '../lexer/SymbolTable.php';
require_once '../lexer/SymbolDecypher.php';

require_once '../ast/Ast.php';
require_once '../ast/PrintStmt.php';
require_once '../ast/Expr.php';

require_once '../parser/Parser.php';
require_once '../parser/TokenReader.php';

use \UranoCompiler\Lexer\Tokenizer;
use \UranoCompiler\Parser\TokenReader;


$lexer = new Tokenizer("print 20");
$parser = new TokenReader($lexer);

$parser->parse();
$parser->ast();

echo PHP_EOL;
