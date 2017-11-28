<?php
/**
 * Quack Compiler and toolkit
 * Copyright (C) 2015-2017 Quack and CONTRIBUTORS
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
define('PRETTY', 'pretty');
define('TYPECHECKER', 'typechecker');
define('AST', 'ast');
define('LEXER', 'lexer');
define('PARSELETS', 'parselets');
define('PARSER', 'parser');
define('SCOPE', 'scope');
define('TYPES', 'types');
define('INTL', 'intl');
define('CLI', 'cli');
define('DS', 'ds');

function import($module, $file)
{
    require_once dirname(__FILE__) . '/../' . $module . '/' . $file . '.php';
}

/* Pretty */
import(PRETTY, 'Colorizer');
import(PRETTY, 'CliColorizer');
import(PRETTY, 'Parenthesized');

/* Type checker */
import(TYPECHECKER, 'FnTypeChecker');
import(TYPECHECKER, 'ListTypeChecker');
import(TYPECHECKER, 'MapTypeChecker');
import(TYPECHECKER, 'NameTypeChecker');
import(TYPECHECKER, 'ObjectTypeChecker');
import(TYPECHECKER, 'TupleTypeChecker');

/* Cli */
import(CLI, 'Component');
import(CLI, 'Croak');
import(CLI, 'Console');
import(CLI, 'Repl');

/* Internationalization */

import(INTL, 'Localization');

/* Lexer */

import(LEXER, 'Lexer');
import(LEXER, 'SymbolDecypher');
import(LEXER, 'Tag');
import(LEXER, 'Token');
import(LEXER, 'Tokenizer');
import(LEXER, 'Word');

import(PARSELETS, 'Parselet');
import(PARSELETS, 'InfixParselet');
import(PARSELETS, 'PrefixParselet');
import(PARSELETS, 'expr/PrefixOperatorParselet');
import(PARSELETS, 'expr/BinaryParselet');
import(PARSELETS, 'expr/PostfixOperatorParselet');
import(PARSELETS, 'expr/TernaryParselet');
import(PARSELETS, 'expr/GroupParselet');
import(PARSELETS, 'expr/LambdaParselet');
import(PARSELETS, 'expr/ListParselet');
import(PARSELETS, 'expr/NameParselet');
import(PARSELETS, 'expr/MemberAccessParselet');
import(PARSELETS, 'expr/CallParselet');
import(PARSELETS, 'expr/AccessParselet');
import(PARSELETS, 'expr/RangeParselet');
import(PARSELETS, 'expr/LiteralParselet');
import(PARSELETS, 'expr/PartialFuncParselet');
import(PARSELETS, 'expr/WhereParselet');
import(PARSELETS, 'expr/MapParselet');
import(PARSELETS, 'expr/MatchParselet');
import(PARSELETS, 'expr/ObjectParselet');
import(PARSELETS, 'expr/BlockParselet');
import(PARSELETS, 'expr/TupleParselet');

import(PARSELETS, 'types/BinaryTypeParselet');
import(PARSELETS, 'types/FnTypeParselet');
import(PARSELETS, 'types/GroupTypeParselet');
import(PARSELETS, 'types/ListTypeParselet');
import(PARSELETS, 'types/MapTypeParselet');
import(PARSELETS, 'types/NameTypeParselet');
import(PARSELETS, 'types/ObjectTypeParselet');
import(PARSELETS, 'types/TupleTypeParselet');

/* Parser */

import(PARSER, 'Attachable');
import(PARSER, 'DeclParser');
import(PARSER, 'ExprParser');
import(PARSER, 'NameParser');
import(PARSER, 'StmtParser');
import(PARSER, 'TypeParser');
import(PARSER, 'Parser');
import(PARSER, 'SyntaxError');
import(PARSER, 'EOFError');
import(PARSER, 'TokenReader');
import(PARSER, 'Precedence');

/* Ast */

import(AST, 'Decl');
import(AST, 'Expr');
import(AST, 'Stmt');
import(AST, 'TypeSig');

import(AST, 'location/Position');
import(AST, 'location/SourceLocation');

import(AST, 'Node');

import(AST, 'helpers/Body');
import(AST, 'helpers/DataMember');
import(AST, 'helpers/Elif');
import(AST, 'helpers/Param');
import(AST, 'helpers/Program');

import(AST, 'expr/ListExpr');
import(AST, 'expr/LambdaExpr');
import(AST, 'expr/MapExpr');
import(AST, 'expr/ObjectExpr');
import(AST, 'expr/NameExpr');
import(AST, 'expr/NumberExpr');
import(AST, 'expr/PrefixExpr');
import(AST, 'expr/BinaryExpr');
import(AST, 'expr/PostfixExpr');
import(AST, 'expr/TernaryExpr');
import(AST, 'expr/StringExpr');
import(AST, 'expr/CallExpr');
import(AST, 'expr/AccessExpr');
import(AST, 'expr/RangeExpr');
import(AST, 'expr/AtomExpr');
import(AST, 'expr/PartialFuncExpr');
import(AST, 'expr/RegexExpr');
import(AST, 'expr/WhereExpr');
import(AST, 'expr/BlockExpr');
import(AST, 'expr/TupleExpr');
import(AST, 'expr/MatchExpr');
import(AST, 'expr/TypeExpr');

import(AST, 'decl/DataDecl');
import(AST, 'decl/FnShortDecl');
import(AST, 'decl/LetDecl');
import(AST, 'decl/TypeDecl');

import(AST, 'stmt/BlockStmt');
import(AST, 'stmt/BreakStmt');
import(AST, 'stmt/ContinueStmt');
import(AST, 'stmt/ExprStmt');
import(AST, 'stmt/ForeachStmt');
import(AST, 'stmt/IfStmt');
import(AST, 'stmt/LabelStmt');
import(AST, 'stmt/ReturnStmt');
import(AST, 'stmt/WhileStmt');

import(AST, 'typesig/BinaryTypeSig');
import(AST, 'typesig/FnTypeSig');
import(AST, 'typesig/ListTypeSig');
import(AST, 'typesig/MapTypeSig');
import(AST, 'typesig/NameTypeSig');
import(AST, 'typesig/ObjectTypeSig');
import(AST, 'typesig/TupleTypeSig');

/* Scope */

import(SCOPE, 'Scope');
import(SCOPE, 'ScopeError');
import(SCOPE, 'Symbol');
import(SCOPE, 'Meta');

/* General data structures */
import(DS, 'Set');

/* Type inference and checking */

import(TYPES, 'Type');
import(TYPES, 'TypeError');

import(TYPES, 'TypeVar');
import(TYPES, 'TypeOperator');

import(TYPES, 'FnType');
import(TYPES, 'GenericType');
import(TYPES, 'ListType');
import(TYPES, 'MapType');
import(TYPES, 'NameType');
import(TYPES, 'ObjectType');
import(TYPES, 'TupleType');
