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
import(PARSER, 'Grammar');
import(PARSER, 'TokenChecker');
import(PARSER, 'SyntaxError');
import(PARSER, 'TokenReader');
import(PARSER, 'Precedence');

import(PARSELETS, 'IInfixParselet');
import(PARSELETS, 'IPrefixParselet');
import(PARSELETS, 'NumberParselet');
import(PARSELETS, 'PrefixOperatorParselet');
import(PARSELETS, 'BinaryOperatorParselet');
import(PARSELETS, 'PostfixOperatorParselet');
import(PARSELETS, 'TernaryParselet');
import(PARSELETS, 'GroupParselet');
import(PARSELETS, 'FunctionParselet');
import(PARSELETS, 'IncludeParselet');
import(PARSELETS, 'ArrayParselet');
import(PARSELETS, 'NameParselet');
import(PARSELETS, 'NewParselet');
import(PARSELETS, 'MemberAccessParselet');

/* Ast */

import(AST, 'Util');
import(AST, 'Node');

import(AST, 'expr/Expr');
import(AST, 'expr/ArrayExpr');
import(AST, 'expr/ArrayPairExpr');
import(AST, 'expr/IncludeExpr');
import(AST, 'expr/LambdaExpr');
import(AST, 'expr/NameExpr');
import(AST, 'expr/NewExpr');
import(AST, 'expr/NumberExpr');
import(AST, 'expr/PrefixExpr');
import(AST, 'expr/OperatorExpr');
import(AST, 'expr/PostfixExpr');
import(AST, 'expr/TernaryExpr');

import(AST, 'stmt/Stmt');
import(AST, 'stmt/BlockStmt');
import(AST, 'stmt/BreakStmt');
import(AST, 'stmt/CaseStmt');
import(AST, 'stmt/ClassStmt');
import(AST, 'stmt/ConstStmt');
import(AST, 'stmt/ContinueStmt');
import(AST, 'stmt/FnStmt');
import(AST, 'stmt/DoWhileStmt');
import(AST, 'stmt/ElifStmt');
import(AST, 'stmt/ExprStmt');
import(AST, 'stmt/ForeachStmt');
import(AST, 'stmt/GlobalStmt');
import(AST, 'stmt/GotoStmt');
import(AST, 'stmt/IfStmt');
import(AST, 'stmt/IntfStmt');
import(AST, 'stmt/LabelStmt');
import(AST, 'stmt/LetStmt');
import(AST, 'stmt/ModuleStmt');
import(AST, 'stmt/OpenStmt');
import(AST, 'stmt/OutStmt');
import(AST, 'stmt/PieceStmt');
import(AST, 'stmt/PrintStmt');
import(AST, 'stmt/PropertyStmt');
import(AST, 'stmt/RaiseStmt');
import(AST, 'stmt/RescueStmt');
import(AST, 'stmt/ReturnStmt');
import(AST, 'stmt/StructStmt');
import(AST, 'stmt/SwitchStmt');
import(AST, 'stmt/TryStmt');
import(AST, 'stmt/WhileStmt');
import(AST, 'stmt/YieldStmt');
import(AST, 'helper/Param');
