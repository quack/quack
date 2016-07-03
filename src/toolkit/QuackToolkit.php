<?php
/**
 * Quack Compiler and toolkit
 * Copyright (C) 2016 Marcelo Camargo <marcelocamargo@linuxmail.org> and
 * CONTRIBUTORS.
 *
 * This file is part of Quack.
 *
 * Quack is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Quack is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Quack.  If not, see <http://www.gnu.org/licenses/>.
 */
define('AST', 'ast');
define('LEXER', 'lexer');
define('PARSELETS', 'parselets');
define('PARSER', 'parser');

function import($module, $file)
{
    require_once BASE_PATH . '/' . $module . '/' . $file . '.php';
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
import(PARSELETS, 'WhenParselet');
import(PARSELETS, 'CallParselet');
import(PARSELETS, 'AccessParselet');
import(PARSELETS, 'RangeParselet');
import(PARSELETS, 'LiteralParselet');
import(PARSELETS, 'PartialFuncParselet');

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
import(AST, 'expr/NilExpr');
import(AST, 'expr/BoolExpr');
import(AST, 'expr/WhenExpr');
import(AST, 'expr/StringExpr');
import(AST, 'expr/CallExpr');
import(AST, 'expr/AccessExpr');
import(AST, 'expr/RangeExpr');
import(AST, 'expr/AtomExpr');
import(AST, 'expr/PartialFuncExpr');
import(AST, 'expr/RegexExpr');

import(AST, 'stmt/Stmt');
import(AST, 'stmt/BlockStmt');
import(AST, 'stmt/BreakStmt');
import(AST, 'stmt/CaseStmt');
import(AST, 'stmt/BlueprintStmt');
import(AST, 'stmt/ConstStmt');
import(AST, 'stmt/ContinueStmt');
import(AST, 'stmt/FnStmt');
import(AST, 'stmt/ForStmt');
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
import(AST, 'stmt/PieceStmt');
import(AST, 'stmt/PrintStmt');
import(AST, 'stmt/MemberStmt');
import(AST, 'stmt/RaiseStmt');
import(AST, 'stmt/RescueStmt');
import(AST, 'stmt/ReturnStmt');
import(AST, 'stmt/StructStmt');
import(AST, 'stmt/SwitchStmt');
import(AST, 'stmt/TryStmt');
import(AST, 'stmt/WhileStmt');
import(AST, 'helper/Param');
