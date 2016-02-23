<?php

namespace QuackCompiler\Parser;

use \QuackCompiler\Lexer\Tag;

use \QuackCompiler\Ast\Stmt\BlockStmt;
use \QuackCompiler\Ast\Stmt\BreakStmt;
use \QuackCompiler\Ast\Stmt\ConstStmt;
use \QuackCompiler\Ast\Stmt\ContinueStmt;
use \QuackCompiler\Ast\Stmt\DefStmt;
use \QuackCompiler\Ast\Stmt\ExprStmt;
use \QuackCompiler\Ast\Stmt\ForeachStmt;
use \QuackCompiler\Ast\Stmt\GlobalStmt;
use \QuackCompiler\Ast\Stmt\GotoStmt;
use \QuackCompiler\Ast\Stmt\IfStmt;
use \QuackCompiler\Ast\Stmt\LabelStmt;
use \QuackCompiler\Ast\Stmt\ModuleStmt;
use \QuackCompiler\Ast\Stmt\OpenStmt;
use \QuackCompiler\Ast\Stmt\PrintStmt;
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

  function _topStmt()
  {
    if ($this->checker->startsStmt())          return $this->_stmt();
    if ($this->checker->startsClassDeclStmt()) return $this->_classDeclStmt();
    if ($this->parser->is(Tag::T_DEF))         return $this->_defStmt();
    if ($this->parser->is(Tag::T_MODULE))      return $this->_moduleStmt();
    if ($this->parser->is(Tag::T_OPEN))        return $this->_openStmt();
    if ($this->parser->is(Tag::T_CONST))       return $this->_constStmt();
  }

  function _defStmt()
  {
    $this->parser->match(Tag::T_DEF);
    $by_reference = false;
    if ($this->parser->is('*')) {
      $this->parser->consume();
      $by_reference = true;
    }
    $name = $this->identifier();
    $parameters = $this->_parameters();
    $body = $this->_innerStmt();

    return new DefStmt($name, $by_reference, $body, $parameters);
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

  function _innerStmt()
  {
    // TODO
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
