<?php

namespace UranoCompiler\Parser;

use \Exception;

use \UranoCompiler\Lexer\Tag;
use \UranoCompiler\Lexer\Tokenizer;

use \UranoCompiler\Ast\BlockStmt;
use \UranoCompiler\Ast\Expr;
use \UranoCompiler\Ast\FunctionDecl;
use \UranoCompiler\Ast\GlobalStmt;
use \UranoCompiler\Ast\GotoStmt;
use \UranoCompiler\Ast\IfStmt;
use \UranoCompiler\Ast\LabelStmt;
use \UranoCompiler\Ast\ModuleStmt;
use \UranoCompiler\Ast\OpenStmt;
use \UranoCompiler\Ast\PrintStmt;

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
        || $this->is('[');
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
    while ($this->startsStmt()) {
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
    if ($this->is(Tag::T_MODULE)) return $this->_module();
    if ($this->is(Tag::T_OPEN))   return $this->_open();
    if ($this->is(':-'))          return $this->_label();
    if ($this->is(Tag::T_GOTO))   return $this->_goto();
    if ($this->is(Tag::T_GLOBAL)) return $this->_global();
    if ($this->is(Tag::T_IF))     return $this->_if();
    if ($this->is('['))           return $this->_blockStmt();
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

  private function _expr()
  {
    return new Expr($this->resolveScope($this->match(Tag::T_INTEGER)));
  }
}
