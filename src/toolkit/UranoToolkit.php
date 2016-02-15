<?php

define('AST', 'ast');
define('LEXER', 'lexer');
define('PARSELETS', 'parselets');
define('PARSER', 'parser');

function import($module, $file)
{
  require_once '../' . $module . '/' . $file . '.php';
}

/* Lexer */

import(LEXER, 'Lexer');
import(LEXER, 'SymbolDecypher');
import(LEXER, 'SymbolTable');
import(LEXER, 'Tag');
import(LEXER, 'Token');
import(LEXER, 'Tokenizer');
import(LEXER, 'Word');

/* Parser */

import(PARSER, 'Parser');
import(PARSER, 'SyntaxError');
import(PARSER, 'TokenReader');

import(PARSELETS, 'IInfixParselet');
import(PARSELETS, 'IPrefixParselet');
import(PARSELETS, 'NumberParselet');
import(PARSELETS, 'PrefixOperatorParselet');

/* Ast */

import(AST, 'Node');

import(AST, 'expr/Expr');
import(AST, 'expr/NumberExpr');
import(AST, 'expr/PrefixExpr');

import(AST, 'stmt/Stmt');
import(AST, 'stmt/BlockStmt');
import(AST, 'stmt/BreakStmt');
import(AST, 'stmt/ContinueStmt');
import(AST, 'stmt/DefStmt');
import(AST, 'stmt/ExprStmt');
import(AST, 'stmt/ForeachStmt');
import(AST, 'stmt/GlobalStmt');
import(AST, 'stmt/IfStmt');
import(AST, 'stmt/LabelStmt');
import(AST, 'stmt/ModuleStmt');
import(AST, 'stmt/OpenStmt');
import(AST, 'stmt/PrintStmt');
import(AST, 'stmt/RaiseStmt');
import(AST, 'stmt/ReturnStmt');
import(AST, 'stmt/WhileStmt');

import(AST, 'helper/Param');
