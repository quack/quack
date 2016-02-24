<?php

namespace QuackCompiler\Parser;

use \Exception;

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Tokenizer;

use \QuackCompiler\Ast\Stmt\BlockStmt;
use \QuackCompiler\Ast\Stmt\BreakStmt;
use \QuackCompiler\Ast\Stmt\ContinueStmt;
use \QuackCompiler\Ast\Stmt\DefStmt;
use \QuackCompiler\Ast\Stmt\ExprStmt;
use \QuackCompiler\Ast\Stmt\ForeachStmt;
use \QuackCompiler\Ast\Stmt\GlobalStmt;
use \QuackCompiler\Ast\Stmt\GotoStmt;
use \QuackCompiler\Ast\Stmt\IfStmt;
use \QuackCompiler\Ast\Stmt\LabelStmt;
use \QuackCompiler\Ast\Stmt\PrintStmt;
use \QuackCompiler\Ast\Stmt\RaiseStmt;
use \QuackCompiler\Ast\Stmt\ReturnStmt;
use \QuackCompiler\Ast\Stmt\WhileStmt;

use \QuackCompiler\Ast\Helper\Param;

class TokenReader extends Parser
{
  public $ast = [];
  public $grammar;

  public function __construct(Tokenizer $input)
  {
    parent::__construct($input);
    $this->grammar = new Grammar($this);
  }

  /* Handlers */
  public function dumpAst()
  {
    var_dump($this->ast);
  }

  public function format()
  {
    foreach ($this->ast as $stmt) {
      echo $stmt->format($this);
    }
  }

  public function beautify()
  {
    $source = [];
    foreach ($this->ast as $stmt) {
      $source[] = $stmt->format($this);
    }
    return implode($source);
  }

  public function python()
  {
    foreach ($this->ast as $stmt) {
      echo $stmt->python($this);
    }
  }

  public function parse()
  {
    $this->ast = $this->grammar->start();
  }

  /* Productions */
  // private function _stmt()
  // {
  //   if ($this->is(Tag::T_MODULE))   return $this->_module();
  //   if ($this->is(Tag::T_OPEN))     return $this->_open();
  //   if ($this->is(':-'))            return $this->_label();
  //   if ($this->is(Tag::T_GOTO))     return $this->_goto();
  //   if ($this->is(Tag::T_GLOBAL))   return $this->_global();
  //   if ($this->is(Tag::T_IF))       return $this->_if();
  //   if ($this->is('['))             return $this->_blockStmt();
  //   if ($this->is(Tag::T_BREAK))    return $this->_break();
  //   if ($this->is(Tag::T_CONTINUE)) return $this->_continue();
  //   if ($this->is('<<<'))           return $this->_return();
  //   if ($this->is(Tag::T_PRINT))    return $this->_print();
  //   if ($this->is(Tag::T_RAISE))    return $this->_raise();
  //   if ($this->is(Tag::T_WHILE))    return $this->_while();
  //   if ($this->is(Tag::T_FOREACH))  return $this->_foreach();
  //   if ($this->is(Tag::T_SWITCH))   return $this->_switch();
  //   if ($this->is(Tag::T_TRY))      return $this->_try();
  //   if ($this->checker->startsExpr())       {
  //     $expr = $this->_expr();
  //     $this->match(';');
  //     return new ExprStmt($expr);
  //   }
  // }

  // private function _label()
  // {
  //   $this->match(':-');
  //   $label_name = $this->identifier();

  //   return new LabelStmt($label_name);
  // }

  // private function _return()
  // {
  //   $this->match('<<<');
  //   $expression = NULL;

  //   if ($this->checker->startsExpr()) {
  //     $expression = $this->_expr();
  //   }

  //   return new ReturnStmt($expression);
  // }

  // private function _print()
  // {
  //   $this->match(Tag::T_PRINT);
  //   return new PrintStmt($this->_expr());
  // }

  // private function _raise()
  // {
  //   $this->match(Tag::T_RAISE);
  //   return new RaiseStmt($this->_expr());
  // }

  // private function _try()
  // {
  //   $this->match(Tag::T_TRY);
  //   $try = $this->_innerStmt();
  //   $rescues = iterator_to_array($this->_rescues());
  //   $finally = NULL;
  //   if ($this->is(Tag::T_FINALLY)) {
  //     $finally = $this->_finally();
  //   }

  //   // TODO: Use objects to store'em
  //   return [
  //     'try'     => $try,
  //     'rescue'  => $rescues,
  //     'finally' => $finally
  //   ];
  // }

  // private function _rescues()
  // {
  //   while ($this->is(Tag::T_RESCUE)) {
  //     $this->match(Tag::T_RESCUE);
  //     // TODO: Implement name as type
  //     $var_name = $this->identifier();
  //     $body = $this->_innerStmt();

  //     yield [
  //       'variable' => $var_name,
  //       'body'     => $body
  //     ];
  //   }
  // }

  // private function _finally()
  // {
  //   $this->match(Tag::T_FINALLY);
  //   return $this->_innerStmt();
  // }

  // private function _switch()
  // {
  //   $this->match(Tag::T_SWITCH);
  //   $condition = $this->_expr();

  //   $this->match('[');
  //   $cases = iterator_to_array($this->_cases());
  //   $this->match(']');

  //   return [
  //     "condition" => $condition,
  //     "cases"     => $cases
  //   ];
  // }

  // private function _cases()
  // {
  //   while ($this->checker->isCase()) {
  //     if ($this->is(Tag::T_CASE)) {
  //       $this->match(Tag::T_CASE);
  //       $when = $this->_expr();
  //       $then = $this->_innerStmt();

  //       yield [
  //         'is_else' => false,
  //         'when'    => $when,
  //         'then'    => $then
  //       ];

  //     } else {
  //       $this->match(Tag::T_ELSE);

  //       yield [
  //         'is_else' => true,
  //         'when'    => NULL,
  //         'then'    => $this->_innerStmt()
  //       ];
  //     }
  //   }
  // }

  // public function _expr($precedence = 0)
  // {
  //   $token = $this->consumeAndFetch();

  //   $prefix = $this->prefixParseletForToken($token);

  //   if ($prefix == NULL) {
  //     throw (new SyntaxError)
  //       -> expected ('expression')
  //       -> found    ($token)
  //       -> on       ($this->position())
  //       -> source   ($this->input);
  //   }

  //   $left = $prefix->parse($this, $token);

  //   while ($precedence < $this->getPrecedence()) {
  //     $token = $this->consumeAndFetch();
  //     $infix = $this->infixParseletForToken($token);
  //     $left = $infix->parse($this, $left, $token);
  //   }

  //   return $left;
  // }

  // private function getPrecedence()
  // {
  //   $parser = $this->infixParseletForToken($this->lookahead);
  //   return !is_null($parser)
  //     ? $parser->getPrecedence()
  //     : 0;
  // }
}
