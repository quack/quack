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
  // private function _classStmtList()
  // {
  //   while ($this->checker->startsClassStmt()) {
  //     yield $this->_classStmt();
  //   }
  // }

  // private function _innerStmt()
  // {
  //   if ($this->checker->startsStmt()) return $this->_stmt();
  //   if ($this->is(Tag::T_DEF)) return $this->_def();

  //   throw (new SyntaxError)
  //     -> expected ('statement')
  //     -> found    ($this->lookahead)
  //     -> on       ($this->position())
  //     -> source   ($this->input);
  // }

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

  // private function _goto()
  // {
  //   $this->match(Tag::T_GOTO);
  //   $goto_name = $this->identifier();

  //   return new GotoStmt($goto_name);
  // }

  // private function _global()
  // {
  //   $this->match(Tag::T_GLOBAL);
  //   $var_name = $this->identifier();

  //   return new GlobalStmt($var_name);
  // }

  // private function _if()
  // {
  //   $this->match(Tag::T_IF);
  //   $condition = $this->_expr();
  //   // TODO: Change for inner stmt
  //   $body = $this->_topStmt();
  //   $elif = $this->_elifList();
  //   $else = $this->_optElse();

  //   return new IfStmt($condition, $body, $elif, $else);
  // }

  // private function _elifList()
  // {
  //   $list = [];
  //   while ($this->is(Tag::T_ELIF)) {
  //     $this->match(Tag::T_ELIF);
  //     $list[] = [
  //       "condition" => $this->_expr(),
  //       "body"      => $this->_topStmt() // TODO: Change for inner stmt
  //     ];
  //   }
  //   return $list;
  // }

  // private function _optElse()
  // {
  //   if (!$this->is(Tag::T_ELSE)) {
  //     return NULL;
  //   }

  //   $this->match(Tag::T_ELSE);
  //   // TODO: Change for inner stmt
  //   return $this->_topStmt();
  // }

  // private function _blockStmt()
  // {
  //   $body = [];

  //   $this->match('[');
  //   while ($this->checker->startsStmt()) {
  //     $body[] = $this->_stmt();
  //   }
  //   $this->match(']');

  //   return new BlockStmt($body);
  // }

  // private function _break()
  // {
  //   $this->match(Tag::T_BREAK);
  //   $expression = NULL;

  //   if ($this->checker->startsExpr()) {
  //     $expression = $this->_expr();
  //   }

  //   return new BreakStmt($expression);
  // }

  // private function _continue()
  // {
  //   $this->match(Tag::T_CONTINUE);
  //   $expression = NULL;

  //   if ($this->checker->startsExpr()) {
  //     $expression = $this->_expr();
  //   }

  //   return new ContinueStmt($expression);
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

  // private function _while()
  // {
  //   $this->match(Tag::T_WHILE);
  //   $condition = $this->_expr();
  //   $body = $this->_stmt();
  //   return new WhileStmt($condition, $body);
  // }

  // private function _foreach()
  // {
  //   $this->match(Tag::T_FOREACH);
  //   $by_ref = false;

  //   if ($this->is('*')) {
  //     $by_ref = true;
  //     $this->match('*');
  //   }

  //   $alias = $this->identifier();
  //   $this->match(Tag::T_IN);
  //   $generator = $this->_expr();
  //   $body = $this->_stmt();

  //   return new ForeachStmt($by_ref, $alias, $generator, $body);
  // }

  // private function _def()
  // {
  //   $this->match(Tag::T_DEF);
  //   $by_ref = false;
  //   if ($this->is('*')) {
  //     $this->match('*');
  //     $by_ref = true;
  //   }
  //   $name = $this->identifier();
  //   $parameters = $this->_parameters();
  //   $body = $this->_innerStmt();

  //   return new DefStmt($name, $by_ref, $body, $parameters);
  // }

  // private function _classStmt()
  // {
  //   $modifiers = iterator_to_array($this->_optMethodModifiers());

  //   if ($this->is(Tag::T_DEF)) {
  //     return $this->_method();
  //   } else {
  //     // Property
  //   }
  // }

  // private function _method()
  // {
  //   $this->match(Tag::T_DEF);
  //   $by_ref = false;
  //   if ($this->is('*')) {
  //     $this->match('*');
  //     $by_ref = true;
  //   }
  //   $name = $this->identifier();
  //   $parameters = $this->_parameters();

  //   $body = $this->checker->startsStmt() ? $this->_innerStmt() : NULL;

  //   // TODO: Return method declaration with modifiers
  //   return new DefStmt($name, $by_ref, $body, $parameters);
  // }

  // private function _optMethodModifiers()
  // {
  //   while ($this->isMethodModifier()) {
  //     $mod = $this->lookahead->lexeme;
  //     $this->consume();
  //     yield $mod;
  //   }
  // }

  // private function _parameters()
  // {
  //   $parameters = [];

  //   if ($this->is('!')) {
  //     $this->match('!');
  //     return $parameters;
  //   }

  //   $this->match('[');

  //   while ($this->checker->startsParameter()) {
  //     $parameters[] = $this->_parameter();

  //     if ($this->is(';')) {
  //       $this->match(';');
  //       // TODO: If no more params, throw error with trailing (;)
  //     } else {
  //       break;
  //     }
  //   }

  //   $this->match(']');
  //   return $parameters;
  // }

  // private function _parameter()
  // {
  //   $ellipsis = false;
  //   $by_ref = false;

  //   if ($ellipsis = $this->is('...')) {
  //     $this->match('...');
  //   }

  //   if ($by_ref = $this->is('*')) {
  //     $this->match('*');
  //   }

  //   $name = $this->resolveScope($this->match(Tag::T_IDENT));

  //   return new Param($name, $by_ref, $ellipsis);
  // }

  // private function _class()
  // {
  //   $type = NULL;
  //   $extends = NULL;
  //   $implements = [];

  //   switch ($this->lookahead->getTag()) {
  //     case Tag::T_CLASS:
  //       $type = 'class';
  //       break;
  //     case Tag::T_MODEL:
  //       $type = 'model';
  //       break;
  //     case Tag::T_FINAL:
  //       $type = 'final';
  //       break;
  //     default:
  //       throw new TodoError(); // TODO
  //   }

  //   $this->consume();
  //   $name = $this->identifier();

  //   if ($this->is(':')) {
  //     $this->match(':');
  //     $extends = $this->identifier();
  //   }

  //   if ($this->is('#')) {
  //     $this->match('#');
  //     $implements[] = $this->identifier();

  //     while ($this->is(';')) {
  //       $this->match(';');
  //       $implements[] = $this->identifier();
  //     }
  //   }

  //   $this->match('[');
  //   $body = iterator_to_array($this->_classStmtList());
  //   $this->match(']');

  //   return [
  //     'type'       => $type,
  //     'name'       => $name,
  //     'extends'    => $extends,
  //     'implements' => $implements,
  //     'body'       => $body
  //   ];
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
