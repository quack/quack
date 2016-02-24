<?php

namespace QuackCompiler\Parser;

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;

use \QuackCompiler\Ast\Stmt\BlockStmt;
use \QuackCompiler\Ast\Stmt\BreakStmt;
use \QuackCompiler\Ast\Stmt\ConstStmt;
use \QuackCompiler\Ast\Stmt\ContinueStmt;
use \QuackCompiler\Ast\Stmt\DefStmt;
use \QuackCompiler\Ast\Stmt\DoWhileStmt;
use \QuackCompiler\Ast\Stmt\ElifStmt;
use \QuackCompiler\Ast\Stmt\ExprStmt;
use \QuackCompiler\Ast\Stmt\ForeachStmt;
use \QuackCompiler\Ast\Stmt\GlobalStmt;
use \QuackCompiler\Ast\Stmt\GotoStmt;
use \QuackCompiler\Ast\Stmt\IfStmt;
use \QuackCompiler\Ast\Stmt\LabelStmt;
use \QuackCompiler\Ast\Stmt\ModuleStmt;
use \QuackCompiler\Ast\Stmt\OpenStmt;
use \QuackCompiler\Ast\Stmt\PrintStmt;
use \QuackCompiler\Ast\Stmt\PropertyStmt;
use \QuackCompiler\Ast\Stmt\RaiseStmt;
use \QuackCompiler\Ast\Stmt\ReturnStmt;
use \QuackCompiler\Ast\Stmt\WhileStmt;

use \QuackCompiler\Ast\Helper\Param;

class Grammar
{
  private $parser;
  private $checker;

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

  function _stmt()
  {
    if ($this->parser->is(Tag::T_IF))       return $this->_ifStmt();
    if ($this->parser->is(Tag::T_WHILE))    return $this->_whileStmt();
    if ($this->parser->is(Tag::T_DO))       return $this->_doWhileStmt();
    if ($this->parser->is(Tag::T_FOR))      return $this->_forStmt();
    if ($this->parser->is(Tag::T_FOREACH))  return $this->_foreachStmt();
    if ($this->parser->is(Tag::T_BREAK))    return $this->_breakStmt();
    if ($this->parser->is(Tag::T_CONTINUE)) return $this->_continueStmt();
    if ($this->parser->is(Tag::T_GOTO))     return $this->_gotoStmt();
    if ($this->parser->is(Tag::T_GLOBAL))   return $this->_globalStmt();
    if ($this->parser->is('['))             return $this->_stmtBlock();

    throw new \Exception('Not a statement');
  }

  function _stmtBlock()
  {
    $this->parser->match('[');
    $body = iterator_to_array($this->_innerStmtList());
    $this->parser->match(']');

    return new BlockStmt($body);
  }

  function _ifStmt()
  {
    $this->parser->match(Tag::T_IF);
    $condition = $this->_expr();
    $body = $this->_stmt();
    $elif = iterator_to_array($this->_elifList());
    $else = $this->_optElse();

    return new IfStmt($condition, $body, $elif, $else);
  }

  function _whileStmt()
  {
    $this->parser->match(Tag::T_WHILE);
    $condition = $this->_expr();
    $body = $this->_stmt();

    return new WhileStmt($condition, $body);
  }

  function _forStmt()
  {
    throw new \Exception('TODO. Open issue to check the best syntax');
  }

  function _foreachStmt()
  {
    $this->parser->match(Tag::T_FOREACH);
    $key = NULL;

    if ($this->parser->is(Tag::T_ATOM)) {
      $key = $this->parser->consumeAndFetch();
    }

    ($by_reference = $this->parser->is('*')) && /* then */ $this->parser->consume();
    $alias = $this->identifier();
    $this->parser->match(Tag::T_IN);
    $iterable = $this->_expr();
    $body = $this->_stmt();

    return new ForeachStmt($by_reference, $key, $alias, $iterable, $body);
  }

  function _doWhileStmt()
  {
    $this->parser->match(Tag::T_DO);
    $body = $this->_stmt();
    $this->parser->match(Tag::T_WHILE);
    $condition = $this->_expr();

    return new DoWhileStmt($condition, $body);
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

  function _expr($precedence = 0)
  {
    $token = $this->parser->consumeAndFetch();
    return new \QuackCompiler\Ast\Expr\NumberExpr($token);
  }

  function _topStmt()
  {
    if ($this->checker->startsStmt())          return $this->_stmt();
    if ($this->checker->startsClassDeclStmt()) return $this->_classDeclStmt();
    if ($this->parser->is(Tag::T_DEF))         return $this->_defStmt();
    if ($this->parser->is(Tag::T_MODULE))      return $this->_moduleStmt();
    if ($this->parser->is(Tag::T_OPEN))        return $this->_openStmt();
    if ($this->parser->is(Tag::T_CONST))       return $this->_constStmt();
  }

  function _innerStmt()
  {
    if ($this->checker->startsStmt())          return $this->_stmt();
    if ($this->parser->is(Tag::T_DEF))         return $this->_defStmt();
    if ($this->checker->startsClassDeclStmt()) return $this->_classDeclStmt();
  }

  function _classStmt()
  {
    if ($this->parser->is(Tag::T_CONST)) return $this->_constStmt();
    if ($this->parser->is(Tag::T_OPEN))  return $this->_openStmt(); // TODO: Replace by traits
    if ($this->parser->is(Tag::T_DEF))   return $this->_defStmt();
    if ($this->parser->is(Tag::T_IDENT)) return $this->_property();

    if ($this->checker->isMethodModifier()) {
      $modifiers = [];
      while ($this->checker->isMethodModifier()) {
        $modifiers[] = $this->parser->consumeAndFetch()->lexeme;
      }

      if ($this->parser->is(Tag::T_DEF))   return $this->_defStmt($modifiers);
      if ($this->parser->is(Tag::T_IDENT)) return $this->_property($modifiers);
    }
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
    $category = 'class';
    $extends = NULL;
    $implements = [];

    switch ($this->parser->lookahead->getTag()) {
      case Tag::T_MODEL:
        $category = 'model';
        break;
      case Tag::T_FINAL:
        $category = 'final';
        break;
    }

    $this->parser->consume();
    $class_name = $this->identifier();

    if ($this->parser->is(':')) {
      $this->parser->consume();
      $extends = $this->identifier();
      // TODO: Change for qualified class name when ready
    }

    if ($this->parser->is('#')) {
      do {
        $this->parser->consume();
        $implements[] = $this->identifier();
      } while ($this->parser->is(';'));
    }

    $this->parser->match('[');
    $body = iterator_to_array($this->_classStmtList());
    $this->parser->match(']');

    return NULL;
  }

  function _defStmt($modifiers = [])
  {
    $this->parser->match(Tag::T_DEF);
    $by_reference = false;
    if ($this->parser->is('*')) {
      $this->parser->consume();
      $by_reference = true;
    }
    $name = $this->identifier();
    $parameters = $this->_parameters();
    $this->parser->match('[');
    $body = iterator_to_array($this->_innerStmtList());
    $this->parser->match(']');

    return new DefStmt($name, $by_reference, $body, $parameters, $modifiers);
  }

  function _moduleStmt()
  {
    $this->parser->match(Tag::T_MODULE);
    return new ModuleStmt($this->qualifiedName());
  }

  function _openStmt()
  {
    $this->parser->match(Tag::T_OPEN);
    $name = $this->qualifiedName();
    $alias = NULL;

    if ($this->parser->is(Tag::T_AS)) {
      $this->parser->consume();
      $alias = $this->identifier();
    }

    return new OpenStmt($name, $alias);
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
