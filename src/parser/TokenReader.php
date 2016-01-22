<?php

namespace UranoCompiler\Parser;

use \Exception;

use \UranoCompiler\Lexer\Tag;
use \UranoCompiler\Lexer\Tokenizer;

use \UranoCompiler\Ast\BlockStmt;
use \UranoCompiler\Ast\BreakStmt;
use \UranoCompiler\Ast\ContinueStmt;
use \UranoCompiler\Ast\Expr;
use \UranoCompiler\Ast\ForeachStmt;
use \UranoCompiler\Ast\FunctionDecl;
use \UranoCompiler\Ast\GlobalStmt;
use \UranoCompiler\Ast\GotoStmt;
use \UranoCompiler\Ast\IfStmt;
use \UranoCompiler\Ast\LabelStmt;
use \UranoCompiler\Ast\ModuleStmt;
use \UranoCompiler\Ast\OpenStmt;
use \UranoCompiler\Ast\PrintStmt;
use \UranoCompiler\Ast\RaiseStmt;
use \UranoCompiler\Ast\ReturnStmt;
use \UranoCompiler\Ast\WhileStmt;

class TokenReader extends Parser
{
  public $ast = [];

  public function __construct(Tokenizer $input)
  {
    parent::__construct($input);
  }

  /* Handlers */
  public function dumpAst()
  {
    var_dump($this->ast);
  }

  public function guiAst($tree = NULL)
  {
    $starts = $tree === NULL;
    $tree = $tree ?: $this->ast;

    if ($starts) {
      $buffer = ["[Program "];
      foreach ($this->ast as $stmt) {
        $buffer[] = $this->guiAst($stmt);
      }
      $buffer[] = "]";
      return implode($buffer);
    }

    return "[" . get_class($tree) . "]";
  }

  public function parse()
  {
    foreach ($this->_topStmtList() as $stmt) {
      $this->ast[] = $stmt;
    }
  }

  /* Checkers */
  private function startsStmt()
  {
    return $this->is(Tag::T_MODULE)
        || $this->is(Tag::T_OPEN)
        || $this->is(':-')
        || $this->is(Tag::T_GOTO)
        || $this->is(Tag::T_GLOBAL)
        || $this->is(Tag::T_IF)
        || $this->is('[')
        || $this->is(Tag::T_BREAK)
        || $this->is('<<<')
        || $this->is(Tag::T_PRINT)
        || $this->is(Tag::T_RAISE)
        || $this->is(Tag::T_WHILE)
        || $this->is(Tag::T_FOREACH);
  }

  private function startsTopStmt()
  {
    return $this->startsStmt()
        || $this->is(Tag::T_DEF)
        || $this->startsClass();
  }

  private function startsParameter()
  {
    return $this->is('...')
        || $this->is('*')
        || $this->is(Tag::T_IDENT);
  }

  private function startsClass()
  {
    return $this->is(Tag::T_FINAL)
        || $this->is(Tag::T_MODEL)
        || $this->is(Tag::T_CLASS);
  }

  private function isEOF()
  {
    return $this->lookahead->getTag() === 0;
  }

  /* Coproductions */
  private function qualifiedName()
  {
    $qualified_name = [$this->match(Tag::T_IDENT)];

    while ($this->is('.')) {
      $this->match('.');
      $qualified_name[] = $this->match(Tag::T_IDENT);
    }

    return array_map(function($name) {
      return $this->resolveScope($name);
    }, $qualified_name);
  }

  private function identifier()
  {
    return $this->resolveScope($this->match(Tag::T_IDENT));
  }

  /* Productions */
  private function _topStmtList()
  {
    while ($this->startsTopStmt()) {
      yield $this->_topStmt();
    }

    if (!$this->isEOF()) {
      throw (new SyntaxError)
        -> expected ('statement')
        -> found    ($this->lookahead)
        -> on       ($this->position())
        -> source   ($this->input);
    }
  }

  private function _topStmt()
  {
    if ($this->startsStmt()) return $this->_stmt();

    if ($this->is(Tag::T_DEF)) return $this->_def();
    if ($this->startsClass())  return $this->_class();
  }

  private function _innerStmt()
  {
    if ($this->startsStmt()) return $this->_stmt();

    if ($this->is(Tag::T_DEF)) return $this->_def();

    throw (new SyntaxError)
      -> expected ('statement')
      -> found    ($this->lookahead)
      -> on       ($this->position())
      -> source   ($this->input);
  }

  private function _stmt()
  {
    if ($this->is(Tag::T_MODULE))  return $this->_module();
    if ($this->is(Tag::T_OPEN))    return $this->_open();
    if ($this->is(':-'))           return $this->_label();
    if ($this->is(Tag::T_GOTO))    return $this->_goto();
    if ($this->is(Tag::T_GLOBAL))  return $this->_global();
    if ($this->is(Tag::T_IF))      return $this->_if();
    if ($this->is('['))            return $this->_blockStmt();
    if ($this->is(Tag::T_BREAK))   return $this->_break();
    if ($this->is('<<<'))          return $this->_return();
    if ($this->is(Tag::T_PRINT))   return $this->_print();
    if ($this->is(Tag::T_RAISE))   return $this->_raise();
    if ($this->is(Tag::T_WHILE))   return $this->_while();
    if ($this->is(Tag::T_FOREACH)) return $this->_foreach();
  }

  private function _module()
  {
    $this->match(Tag::T_MODULE);

    return new ModuleStmt($this->qualifiedName());
  }

  private function _open()
  {
    $this->match(Tag::T_OPEN);
    $name = $this->qualifiedName();
    $alias = NULL;

    if ($this->is(Tag::T_AS)) {
      $this->match(Tag::T_AS);
      $alias = $this->identifier();
    }

    return new OpenStmt($name, $alias);
  }

  private function _label()
  {
    $this->match(':-');
    $label_name = $this->identifier();

    return new LabelStmt($label_name);
  }

  private function _goto()
  {
    $this->match(Tag::T_GOTO);
    $goto_name = $this->identifier();

    return new GotoStmt($goto_name);
  }

  private function _global()
  {
    $this->match(Tag::T_GLOBAL);
    $var_name = $this->identifier();

    return new GlobalStmt($var_name);
  }

  private function _if()
  {
    $this->match(Tag::T_IF);
    $condition = $this->_expr();
    // TODO: Change for inner stmt
    $body = $this->_topStmt();
    $elif = $this->_elifList();
    $else = $this->_optElse();

    return new IfStmt($condition, $body, $elif, $else);
  }

  private function _elifList()
  {
    $list = [];
    while ($this->is(Tag::T_ELIF)) {
      $this->match(Tag::T_ELIF);
      $list[] = [
        "condition" => $this->_expr(),
        "body"      => $this->_topStmt() // TODO: Change for inner stmt
      ];
    }
    return $list;
  }

  private function _optElse()
  {
    if (!$this->is(Tag::T_ELSE)) {
      return NULL;
    }

    $this->match(Tag::T_ELSE);
    // TODO: Change for inner stmt
    return $this->_topStmt();
  }

  private function _blockStmt()
  {
    $body = [];

    $this->match('[');
    while ($this->startsStmt()) {
      $body[] = $this->_topStmt();
    }
    $this->match(']');

    return new BlockStmt($body);
  }

  private function _break()
  {
    // TODO: Implement optional expression on BREAK, CONTINUE, RETURN
    $this->match(Tag::T_BREAK);
    return new BreakStmt(NULL);
  }

  private function _return()
  {
    $this->match('<<<');
    return new ReturnStmt(NULL);
  }

  private function _print()
  {
    $this->match(Tag::T_PRINT);
    return new PrintStmt($this->_expr());
  }

  private function _raise()
  {
    $this->match(Tag::T_RAISE);
    return new RaiseStmt($this->_expr());
  }

  private function _while()
  {
    $this->match(Tag::T_WHILE);
    $condition = $this->_expr();
    $body = $this->_topStmt();

    return new WhileStmt($condition, $body);
  }

  private function _foreach()
  {
    $this->match(Tag::T_FOREACH);
    $by_ref = false;

    if ($this->is('*')) {
      $by_ref = true;
      $this->match('*');
    }

    $alias = $this->identifier();
    $this->match(Tag::T_IN);
    $generator = $this->_expr();
    $body = $this->_topStmt();

    return new ForeachStmt($by_ref, $alias, $generator, $body);
  }

  private function _def()
  {
    $this->match(Tag::T_DEF);
    $by_ref = false;
    if ($this->is('*')) {
      $this->match('*');
      $by_ref = true;
    }
    $name = $this->identifier();
    $parameters = $this->_parameters();
    $body = $this->_innerStmt();

    return (new FunctionDecl($name))
      -> byRef      ($by_ref)
      -> body       ($body)
      -> parameters ($parameters);
  }

  private function _parameters()
  {
    $parameters = [];

    if ($this->is('!')) {
      $this->match('!');
      return $parameters;
    }

    $this->match('[');

    while ($this->startsParameter()) {
      $parameters[] = $this->_parameter();

      if ($this->is(';')) {
        $this->match(';');
        // TODO: If no more params, throw error with trailing (;)
      } else {
        break;
      }
    }

    $this->match(']');
    return $parameters;
  }

  private function _parameter()
  {
    $ellipsis = false;
    $by_ref = false;

    if ($ellipsis = $this->is('...')) {
      $this->match('...');
    }

    if ($by_ref = $this->is('*')) {
      $this->match('*');
    }

    $name = $this->resolveScope($this->match(Tag::T_IDENT));

    return [
      "ellipsis" => $ellipsis,
      "by_ref"   => $by_ref,
      "name"     => $name
    ];
  }

  private function _class()
  {
    $type = NULL;
    $extends = NULL;
    $implements = [];

    switch ($this->lookahead->getTag()) {
      case Tag::T_CLASS:
        $type = 'class';
        break;
      case Tag::T_MODEL:
        $type = 'model';
        break;
      case Tag::T_FINAL:
        $type = 'final';
        break;
      default:
        throw new TodoError(); // TODO
    }

    $this->consume();
    $name = $this->identifier();

    if ($this->is(':')) {
      $this->match(':');
      $extends = $this->identifier();
    }

    if ($this->is('#')) {
      $this->match('#');
      $implements[] = $this->identifier();

      while ($this->is(';')) {
        $this->match(';');
        $implements[] = $this->identifier();
      }
    }

    return [
      'type'       => $type,
      'name'       => $name,
      'extends'    => $extends,
      'implements' => $implements
    ];
  }

  private function _expr()
  {
    return new Expr($this->resolveScope($this->match(Tag::T_INTEGER)));
  }
}
