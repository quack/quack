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
define('SCOPE', 'scope');
define('TYPES', 'types');
define('INTL', 'intl');

function import($module, $file)
{
    require_once dirname(__FILE__) . '/../' . $module . '/' . $file . '.php';
}

/* Internationalization */

import(INTL, 'Localization');

/* Lexer */

import(LEXER, 'Lexer');
import(LEXER, 'SymbolDecypher');
import(LEXER, 'SymbolTable');
import(LEXER, 'Tag');
import(LEXER, 'Token');
import(LEXER, 'Tokenizer');
import(LEXER, 'Word');

// Scope base interface
import(SCOPE, 'Membered');

import(PARSELETS, 'Parselet');
import(PARSELETS, 'InfixParselet');
import(PARSELETS, 'PrefixParselet');
import(PARSELETS, 'expr/PrefixOperatorParselet');
import(PARSELETS, 'expr/BinaryOperatorParselet');
import(PARSELETS, 'expr/PostfixOperatorParselet');
import(PARSELETS, 'expr/TernaryParselet');
import(PARSELETS, 'expr/GroupParselet');
import(PARSELETS, 'expr/LambdaParselet');
import(PARSELETS, 'expr/ArrayParselet');
import(PARSELETS, 'expr/NameParselet');
import(PARSELETS, 'expr/NewParselet');
import(PARSELETS, 'expr/MemberAccessParselet');
import(PARSELETS, 'expr/WhenParselet');
import(PARSELETS, 'expr/CallParselet');
import(PARSELETS, 'expr/AccessParselet');
import(PARSELETS, 'expr/RangeParselet');
import(PARSELETS, 'expr/LiteralParselet');
import(PARSELETS, 'expr/PartialFuncParselet');
import(PARSELETS, 'expr/WhereParselet');
import(PARSELETS, 'expr/MapParselet');
import(PARSELETS, 'expr/ObjectParselet');
import(PARSELETS, 'expr/BlockParselet');

import(PARSELETS, 'types/AtomTypeParselet');
import(PARSELETS, 'types/BinaryOperatorTypeParselet');
import(PARSELETS, 'types/FunctionTypeParselet');
import(PARSELETS, 'types/GroupTypeParselet');
import(PARSELETS, 'types/InstanceTypeParselet');
import(PARSELETS, 'types/ListTypeParselet');
import(PARSELETS, 'types/LiteralTypeParselet');
import(PARSELETS, 'types/MapTypeParselet');
import(PARSELETS, 'types/ObjectTypeParselet');
import(PARSELETS, 'types/TupleTypeParselet');

/* Parser */

import(PARSER, 'DeclParser');
import(PARSER, 'TypeParser');
import(PARSER, 'Parser');
import(PARSER, 'Grammar');
import(PARSER, 'TokenChecker');
import(PARSER, 'SyntaxError');
import(PARSER, 'TokenReader');
import(PARSER, 'Precedence');

/* Ast */

import(AST, 'Node');

import(AST, 'expr/Expr');
import(AST, 'expr/ArrayExpr');
import(AST, 'expr/LambdaExpr');
import(AST, 'expr/MapExpr');
import(AST, 'expr/ObjectExpr');
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
import(AST, 'expr/WhereExpr');
import(AST, 'expr/BlockExpr');

import(AST, 'stmt/Stmt');
import(AST, 'stmt/BlockStmt');
import(AST, 'stmt/BreakStmt');
import(AST, 'stmt/CaseStmt');
import(AST, 'stmt/ImplStmt');
import(AST, 'stmt/ConstStmt');
import(AST, 'stmt/ContinueStmt');
import(AST, 'stmt/FnStmt');
import(AST, 'stmt/ForStmt');
import(AST, 'stmt/ElifStmt');
import(AST, 'stmt/EnumStmt');
import(AST, 'stmt/ExprStmt');
import(AST, 'stmt/ForeachStmt');
import(AST, 'stmt/IfStmt');
import(AST, 'stmt/LabelStmt');
import(AST, 'stmt/LetStmt');
import(AST, 'stmt/ModuleStmt');
import(AST, 'stmt/OpenStmt');
import(AST, 'stmt/PostConditionalStmt');
import(AST, 'stmt/ProgramStmt');
import(AST, 'stmt/RaiseStmt');
import(AST, 'stmt/ReturnStmt');
import(AST, 'stmt/ShapeStmt');
import(AST, 'stmt/SwitchStmt');
import(AST, 'stmt/ClassStmt');
import(AST, 'stmt/TryStmt');
import(AST, 'stmt/WhileStmt');
import(AST, 'stmt/StmtList');

import(AST, 'types/TypeNode');
import(AST, 'types/AtomType');
import(AST, 'types/FunctionType');
import(AST, 'types/GenericType');
import(AST, 'types/InstanceType');
import(AST, 'types/ListType');
import(AST, 'types/LiteralType');
import(AST, 'types/MapType');
import(AST, 'types/ObjectType');
import(AST, 'types/OperatorType');
import(AST, 'types/TupleType');

/* Scope */

import(SCOPE, 'Scope');
import(SCOPE, 'ScopeError');
import(SCOPE, 'Kind');
import(SCOPE, 'Meta');

/* Type inference and checking */

import(TYPES, 'NativeQuackType');
import(TYPES, 'Type');
import(TYPES, 'TypeError');
