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
namespace QuackCompiler\Parser;

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;

use \QuackCompiler\Ast\Expr\ArrayPairExpr;

use \QuackCompiler\Ast\Stmt\BlockStmt;
use \QuackCompiler\Ast\Stmt\BreakStmt;
use \QuackCompiler\Ast\Stmt\CaseStmt;
use \QuackCompiler\Ast\Stmt\ClassStmt;
use \QuackCompiler\Ast\Stmt\ConstStmt;
use \QuackCompiler\Ast\Stmt\ContinueStmt;
use \QuackCompiler\Ast\Stmt\FnStmt;
use \QuackCompiler\Ast\Stmt\DoWhileStmt;
use \QuackCompiler\Ast\Stmt\ElifStmt;
use \QuackCompiler\Ast\Stmt\ExprStmt;
use \QuackCompiler\Ast\Stmt\ForeachStmt;
use \QuackCompiler\Ast\Stmt\ForStmt;
use \QuackCompiler\Ast\Stmt\GlobalStmt;
use \QuackCompiler\Ast\Stmt\GotoStmt;
use \QuackCompiler\Ast\Stmt\IfStmt;
use \QuackCompiler\Ast\Stmt\IntfStmt;
use \QuackCompiler\Ast\Stmt\LabelStmt;
use \QuackCompiler\Ast\Stmt\LetStmt;
use \QuackCompiler\Ast\Stmt\ModuleStmt;
use \QuackCompiler\Ast\Stmt\OpenStmt;
use \QuackCompiler\Ast\Stmt\PieceStmt;
use \QuackCompiler\Ast\Stmt\PrintStmt;
use \QuackCompiler\Ast\Stmt\PropertyStmt;
use \QuackCompiler\Ast\Stmt\RaiseStmt;
use \QuackCompiler\Ast\Stmt\RescueStmt;
use \QuackCompiler\Ast\Stmt\ReturnStmt;
use \QuackCompiler\Ast\Stmt\StructStmt;
use \QuackCompiler\Ast\Stmt\SwitchStmt;
use \QuackCompiler\Ast\Stmt\TryStmt;
use \QuackCompiler\Ast\Stmt\WhileStmt;

use \QuackCompiler\Ast\Helper\Param;

class Grammar
{
  public $parser;
  public $checker;

  function __construct(TokenReader $parser)
  {
    $this->parser = $parser;
    $this->checker = new TokenChecker($parser);
  }

  function start()
  {
    return iterator_to_array($this->_topStmtList());
  }

  function _topStmtList()
  {
    while ($this->checker->startsTopStmt()) {
      yield $this->_topStmt();
    }

    if (!$this->checker->isEoF()) {
      throw (new SyntaxError)
        -> expected ('statement')
        -> found    ($this->parser->lookahead)
        -> on       ($this->parser->position())
        -> source   ($this->parser->input);
    }
  }

  function _innerStmtList()
  {
    while ($this->checker->startsInnerStmt()) {
      yield $this->_innerStmt();
    }
  }

  function _classStmtList()
  {
    while ($this->checker->startsClassStmt()) {
      yield $this->_classStmt();
    }
  }

  function _arrayPairList()
  {
    while (!$this->parser->is('}')) {
      $left = $this->_expr();
      $right = NULL;

      if ($this->checker->startsExpr()) {
        $right = $this->_expr();
      }

      if (!$this->parser->is('}')) {
        $this->parser->match(';');
      }

      yield new ArrayPairExpr($left, $right);
    }
  }

  function _stmt()
  {
    $branch_table = [
      Tag::T_IF       => '_ifStmt',
      Tag::T_LET      => '_letStmt',
      Tag::T_WHILE    => '_whileStmt',
      Tag::T_DO       => '_exprStmt',
      Tag::T_FOR      => '_forStmt',
      Tag::T_FOREACH  => '_foreachStmt',
      Tag::T_SWITCH   => '_switchStmt',
      Tag::T_TRY      => '_tryStmt',
      Tag::T_BREAK    => '_breakStmt',
      Tag::T_CONTINUE => '_continueStmt',
      Tag::T_GOTO     => '_gotoStmt',
      Tag::T_GLOBAL   => '_globalStmt',
      Tag::T_RAISE    => '_raiseStmt',
      Tag::T_PRINT    => '_printStmt',
      Tag::T_BEGIN    => '_blockStmt',
      '^'             => '_returnStmt',
      ':-'            => '_labelStmt'
    ];

    foreach ($branch_table as $token => $action) if ($this->parser->is($token)) {
      return call_user_func([$this, $action]);
    }

    throw (new SyntaxError)
      -> expected ('statement')
      -> found    ($this->parser->lookahead)
      -> on       ($this->parser->position())
      -> source   ($this->parser->input);
  }

  function _exprStmt()
  {
    $this->parser->match(Tag::T_DO);
    $expr = $this->_expr();
    return new ExprStmt($expr);
  }

  function _blockStmt()
  {
    $this->parser->match(Tag::T_BEGIN);
    $body = iterator_to_array($this->_innerStmtList());
    $this->parser->match(Tag::T_END);

    return new BlockStmt($body);
  }

  function _ifStmt()
  {
    $this->parser->match(Tag::T_IF);
    $condition = $this->_expr();
    $body = iterator_to_array($this->_innerStmtList());
    $elif = iterator_to_array($this->_elifList());
    $else = $this->_optElse();
    $this->parser->match(Tag::T_END);

    return new IfStmt($condition, $body, $elif, $else);
  }

  function _letStmt()
  {
    $this->parser->match(Tag::T_LET);
    $name = $this->identifier();
    $this->parser->match(':-');
    $value = $this->_expr();

    return new LetStmt($name, $value);
  }

  function _whileStmt()
  {
    $this->parser->match(Tag::T_WHILE);
    $condition = $this->_expr();
    $body = iterator_to_array($this->_innerStmtList());
    $this->parser->match(Tag::T_END);

    return new WhileStmt($condition, $body);
  }

  function _forStmt()
  {
    $this->parser->match(Tag::T_FOR);
    $variable = $this->identifier();
    $this->parser->match(Tag::T_FROM);
    $from = $this->_expr();
    $this->parser->match(Tag::T_TO);
    $to = $this->_expr();
    $by = NULL;

    if ($this->parser->is(Tag::T_BY)) {
      $this->parser->consume();
      $by = $this->_expr();
    }

    $body = iterator_to_array($this->_innerStmtList());
    $this->parser->match(Tag::T_END);

    return new ForStmt($variable, $from, $to, $by, $body);
  }

  function _foreachStmt()
  {
    $this->parser->match(Tag::T_FOREACH);

    ($by_reference = $this->parser->is('*')) && /* then */ $this->parser->consume();
    $alias = $this->identifier();
    $this->parser->match(Tag::T_IN);
    $iterable = $this->_expr();
    $body = iterator_to_array($this->_innerStmtList());
    $this->parser->match(Tag::T_END);

    return new ForeachStmt($by_reference, $alias, $iterable, $body);
  }

  function _switchStmt()
  {
    $this->parser->match(Tag::T_SWITCH);
    $value = $this->_expr();
    $cases = iterator_to_array($this->_caseStmtList());
    $this->parser->match(Tag::T_END);

    return new SwitchStmt($value, $cases);
  }

  function _tryStmt()
  {
    $this->parser->match(Tag::T_TRY);
    $body = iterator_to_array($this->_innerStmtList());
    $rescues = iterator_to_array($this->_rescueStmtList());
    $finally = $this->_optFinally();
    $this->parser->match(Tag::T_END);

    return new TryStmt($body, $rescues, $finally);
  }

  function _breakStmt()
  {
    $this->parser->match(Tag::T_BREAK);
    $expression = NULL;
    if ($this->checker->startsExpr()) {
      $expression = $this->_expr();
    }

    return new BreakStmt($expression);
  }

  function _continueStmt()
  {
    $this->parser->match(Tag::T_CONTINUE);
    $expression = NULL;
    if ($this->checker->startsExpr()) {
      $expression = $this->_expr();
    }

    return new ContinueStmt($expression);
  }

  function _gotoStmt()
  {
    $this->parser->match(Tag::T_GOTO);
    $label = $this->identifier();
    return new GotoStmt($label);
  }

  function _globalStmt()
  {
    $this->parser->match(Tag::T_GLOBAL);
    $variable = $this->identifier();
    return new GlobalStmt($variable);
  }

  function _raiseStmt()
  {
    $this->parser->match(Tag::T_RAISE);
    $expression = $this->_expr();

    return new RaiseStmt($expression);
  }

  function _printStmt()
  {
    $this->parser->match(Tag::T_PRINT);
    $expression = $this->_expr();

    return new PrintStmt($expression);
  }

  function _returnStmt()
  {
    $this->parser->match('^');
    $expression = NULL;

    if ($this->checker->startsExpr()) {
      $expression = $this->_expr();
    }

    return new ReturnStmt($expression);
  }

  function _labelStmt()
  {
    $this->parser->match(':-');
    $label_name = $this->identifier();

    return new LabelStmt($label_name);
  }

  function _elifList()
  {
    while ($this->parser->is(Tag::T_ELIF)) {
      $this->parser->consume();
      $condition = $this->_expr();
      $body = $this->_stmt();
      yield new ElifStmt($condition, $body);
    }
  }

  function _optElse()
  {
    if (!$this->parser->is(Tag::T_ELSE)) {
      return NULL;
    }

    $this->parser->consume();
    return $this->_stmt();
  }

  function _topStmt()
  {
    if ($this->checker->startsStmt())          return $this->_stmt();
    if ($this->checker->startsClassDeclStmt()) return $this->_classDeclStmt();
    if ($this->parser->is(Tag::T_STRUCT))      return $this->_structDeclStmt();
    if ($this->parser->is(Tag::T_FN))          return $this->_fnStmt();
    if ($this->parser->is(Tag::T_MODULE))      return $this->_moduleStmt();
    if ($this->parser->is(Tag::T_OPEN))        return $this->_openStmt();
    if ($this->parser->is(Tag::T_CONST))       return $this->_constStmt();
  }

  function _innerStmt()
  {
    if ($this->checker->startsStmt())          return $this->_stmt();
    if ($this->parser->is(Tag::T_FN))          return $this->_fnStmt();
    if ($this->checker->startsClassDeclStmt()) return $this->_classDeclStmt();
    if ($this->parser->is(Tag::T_STRUCT))      return $this->_structDeclStmt();
  }

  function _classStmt()
  {
    if ($this->parser->is(Tag::T_CONST)) return $this->_constStmt();
    if ($this->parser->is(Tag::T_OPEN))  return $this->_openStmt(); // TODO: Replace by traits
    if ($this->parser->is(Tag::T_FN))    return $this->_fnStmt();
    if ($this->parser->is(Tag::T_IDENT)) return $this->_property();

    throw new \SyntaxError('TODO: Throw a syntax error');
  }

  function _property($modifiers = [])
  {
    $name = $this->identifier();
    $value = NULL;

    if ($this->parser->is(':-')) {
      $this->parser->consume();
      $value = $this->identifier(); // TODO: Change for _staticScalar()
    }

    return new PropertyStmt($name, $value, $modifiers);
  }

  function _classDeclStmt() {
    $extends = NULL;
    $implements = [];

    $this->parser->match(Tag::T_CLASS);
    $class_name = $this->identifier();

    if ($this->parser->is(':')) {
      $this->parser->consume();
      $extends = $this->qualifiedName();
    }

    if ($this->parser->is('#')) {
      do {
        $this->parser->consume();
        $implements[] = $this->qualifiedName();
      } while ($this->parser->is(';'));
    }

    $body = iterator_to_array($this->_classStmtList());
    $this->parser->match(Tag::T_END);

    return new ClassStmt($class_name, $extends, $implements, $body);
  }

  function _structDeclStmt()
  {
    $interfaces = [];

    $this->parser->match(Tag::T_STRUCT);

    if ($this->parser->is(':')) {
      do {
        $this->parser->consume();
        $interfaces[] = $this->qualifiedName();
      } while ($this->parser->is(';'));
    }

    $body = iterator_to_array($this->_classStmtList());
    $this->parser->match(Tag::T_END);
    $name = $this->identifier();

    return new StructStmt($name, $interfaces, $body);
  }

  function _fnStmt($modifiers = [])
  {
    $by_reference = false;
    $this->parser->match(Tag::T_FN);
    if ($this->parser->is('*')) {
      $this->parser->consume();
      $by_reference = true;
    }

    $name = $this->identifier();
    $parameters = $this->_parameters();

    $body = iterator_to_array($this->_innerStmtList());
    $this->parser->match(Tag::T_END);

    return new FnStmt($name, $by_reference, $body, $parameters, $modifiers);
  }

  function _moduleStmt()
  {
    $this->parser->match(Tag::T_MODULE);
    return new ModuleStmt($this->qualifiedName());
  }

  function _openStmt()
  {
    $this->parser->match(Tag::T_OPEN);
    $type = NULL;
    if ($this->parser->is(Tag::T_CONST) || $this->parser->is(Tag::T_FN)) {
      $type = $this->parser->consumeAndFetch();
    }

    $name = $this->parser->is('.') ? [$this->parser->consumeAndFetch()->getTag()] : [];
    $name[] = $this->qualifiedName();
    $alias = NULL;

    if ($this->parser->is(Tag::T_AS)) {
      $this->parser->consume();
      $alias = $this->identifier();
    }

    return new OpenStmt($name, $alias, $type);
  }

  function _constStmt()
  {
    $this->parser->match(Tag::T_CONST);
    $name = $this->identifier();
    $this->parser->match(':-');
    $value = $this->identifier(); // TODO: Change for _staticScalar()
    return new ConstStmt($name, $value);
  }

  function _parameters()
  {
    $parameters = [];

    if ($this->parser->is('!')) {
      $this->parser->consume();
      return $parameters;
    }

    $this->parser->match('[');

    while ($this->checker->startsParameter()) {
      $parameters[] = $this->_parameter();

      if ($this->parser->is(';')) {
        $this->parser->consume();
      } else {
        break;
      }
    }

    $this->parser->match(']');
    return $parameters;
  }

  function _parameter()
  {
    $ellipsis = false;
    $by_reference = false;

    if ($ellipsis = $this->parser->is('...')) {
      $this->parser->consume();
    }

    if ($by_reference = $this->parser->is('*')) {
      $this->parser->consume();
    }

    $name = $this->identifier();

    return new Param($name, $by_reference, $ellipsis);
  }

  function _caseStmtList()
  {
    while ($this->checker->startsCase()) {
      $is_else = $this->parser->is(Tag::T_ELSE);
      $this->parser->consume();
      $value = $is_else ? NULL : $this->_expr();
      $body = iterator_to_array($this->_innerStmtList());

      yield new CaseStmt($value, $body, $is_else);
    }
  }

  function _rescueStmtList()
  {
    while ($this->parser->is(Tag::T_RESCUE)) {
      $this->parser->consume();
      $this->parser->match('[');
      $exception_class = $this->qualifiedName();
      $variable = $this->identifier();
      $this->parser->match(']');
      $body = iterator_to_array($this->_innerStmtList());

      yield new RescueStmt($exception_class, $variable, $body);
    }
  }

  function _optFinally()
  {
    if ($this->parser->is(Tag::T_FINALLY)) {
      $this->parser->consume();
      $body = iterator_to_array($this->_innerStmtList());
      return $body;
    }

    return NULL;
  }

  function _name()
  {
    $name = $this->parser->lookahead;
    $this->parser->match(Tag::T_IDENT);
    return $name;
  }

  function _expr($precedence = 0)
  {
    $token = $this->parser->consumeAndFetch();
    $prefix = $this->parser->prefixParseletForToken($token);

    if (is_null($prefix)) {
      throw (new SyntaxError)
        -> expected ('expression')
        -> found    ($token)
        -> on       ($this->parser->position())
        -> source   ($this->parser->input);
    }

    $left = $prefix->parse($this, $token);

    while ($precedence < $this->getPrecedence()) {
      $token = $this->parser->consumeAndFetch();
      $infix = $this->parser->infixParseletForToken($token);
      $left = $infix->parse($this, $left, $token);
    }

    return $left;
  }

  private function getPrecedence()
  {
    $parser = $this->parser->infixParseletForToken($this->parser->lookahead);
    return !is_null($parser)
      ? $parser->getPrecedence()
      : 0;
  }

  /* Coproductions */
  function qualifiedName()
  {
    $symbol_pointers = [$this->parser->match(Tag::T_IDENT)];
    while ($this->parser->is('.')) {
      $this->parser->consume();
      $symbol_pointers[] = $this->parser->match(Tag::T_IDENT);
    }

    return array_map(function($name) {
      return $this->parser->resolveScope($name);
    }, $symbol_pointers);
  }

  function identifier()
  {
    return $this->parser->resolveScope($this->parser->match(Tag::T_IDENT));
  }
}
