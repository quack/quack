<?php

namespace UranoCompiler\Parser;

use \Exception;

use \UranoCompiler\Lexer\Tag;
use \UranoCompiler\Lexer\Tokenizer;

use \UranoCompiler\Ast\Stmt\BlockStmt;
use \UranoCompiler\Ast\Stmt\BreakStmt;
use \UranoCompiler\Ast\Stmt\ContinueStmt;
use \UranoCompiler\Ast\Stmt\DefStmt;
use \UranoCompiler\Ast\Stmt\ExprStmt;
use \UranoCompiler\Ast\Stmt\ForeachStmt;
use \UranoCompiler\Ast\Stmt\GlobalStmt;
use \UranoCompiler\Ast\Stmt\GotoStmt;
use \UranoCompiler\Ast\Stmt\IfStmt;
use \UranoCompiler\Ast\Stmt\LabelStmt;
use \UranoCompiler\Ast\Stmt\ModuleStmt;
use \UranoCompiler\Ast\Stmt\OpenStmt;
use \UranoCompiler\Ast\Stmt\PrintStmt;
use \UranoCompiler\Ast\Stmt\RaiseStmt;
use \UranoCompiler\Ast\Stmt\ReturnStmt;
use \UranoCompiler\Ast\Stmt\WhileStmt;

use \UranoCompiler\Ast\Helper\Param;

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

  public function format()
  {
    foreach ($this->ast as $stmt) {
      echo $stmt->format($this);
    }
  }

  public function parse()
  {
    $this->ast = iterator_to_array($this->_topStmtList());
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
        || $this->is(Tag::T_CONTINUE)
        || $this->is('<<<')
        || $this->is(Tag::T_PRINT)
        || $this->is(Tag::T_RAISE)
        || $this->is(Tag::T_WHILE)
        || $this->is(Tag::T_FOREACH)
        || $this->is(Tag::T_SWITCH)
        || $this->is(Tag::T_TRY)
        || $this->startsExpr();
  }

  private function startsTopStmt()
  {
    return $this->startsStmt()
        || $this->is(Tag::T_DEF)
        || $this->startsClass();
  }

  private function startsExpr()
  {
    return $this->is(Tag::T_INTEGER)
        || $this->is(Tag::T_DOUBLE)
        || $this->isOperator();
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

  private function isMethodModifier()
  {
    return $this->is(Tag::T_MY)
        || $this->is(Tag::T_PROTECTED)
        || $this->is(Tag::T_STATIC)
        || $this->is(Tag::T_MODEL)
        || $this->is(Tag::T_FINAL);
  }

  private function startsClassStmt()
  {
    return $this->is(Tag::T_DEF)
        || $this->isMethodModifier();
  }

  private function isCase()
  {
    return $this->is(Tag::T_CASE)
        || $this->is(Tag::T_ELSE);
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

  private function _classStmtList()
  {
    while ($this->startsClassStmt()) {
      yield $this->_classStmt();
    }
  }

  private function _topStmt()
  {
    if ($this->startsStmt())   return $this->_stmt();
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
    if ($this->is(Tag::T_MODULE))   return $this->_module();
    if ($this->is(Tag::T_OPEN))     return $this->_open();
    if ($this->is(':-'))            return $this->_label();
    if ($this->is(Tag::T_GOTO))     return $this->_goto();
    if ($this->is(Tag::T_GLOBAL))   return $this->_global();
    if ($this->is(Tag::T_IF))       return $this->_if();
    if ($this->is('['))             return $this->_blockStmt();
    if ($this->is(Tag::T_BREAK))    return $this->_break();
    if ($this->is(Tag::T_CONTINUE)) return $this->_continue();
    if ($this->is('<<<'))           return $this->_return();
    if ($this->is(Tag::T_PRINT))    return $this->_print();
    if ($this->is(Tag::T_RAISE))    return $this->_raise();
    if ($this->is(Tag::T_WHILE))    return $this->_while();
    if ($this->is(Tag::T_FOREACH))  return $this->_foreach();
    if ($this->is(Tag::T_SWITCH))   return $this->_switch();
    if ($this->is(Tag::T_TRY))      return $this->_try();
    if ($this->startsExpr())       {
      $expr = $this->_expr();
      $this->match(';');
      return new ExprStmt($expr);
    }
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
      $body[] = $this->_stmt();
    }
    $this->match(']');

    return new BlockStmt($body);
  }

  private function _break()
  {
    $this->match(Tag::T_BREAK);
    $expression = NULL;

    if ($this->startsExpr()) {
      $expression = $this->_expr();
    }

    return new BreakStmt($expression);
  }

  private function _continue()
  {
    $this->match(Tag::T_CONTINUE);
    $expression = NULL;

    if ($this->startsExpr()) {
      $expression = $this->_expr();
    }

    return new ContinueStmt($expression);
  }

  private function _return()
  {
    $this->match('<<<');
    $expression = NULL;

    if ($this->startsExpr()) {
      $expression = $this->_expr();
    }

    return new ReturnStmt($expression);
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
    $body = $this->_stmt();
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
    $body = $this->_stmt();

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

    return new DefStmt($name, $by_ref, $body, $parameters);
  }

  private function _classStmt()
  {
    $modifiers = iterator_to_array($this->_optMethodModifiers());

    if ($this->is(Tag::T_DEF)) {
      return $this->_method();
    } else {
      // Property
    }
  }

  private function _method()
  {
    $this->match(Tag::T_DEF);
    $by_ref = false;
    if ($this->is('*')) {
      $this->match('*');
      $by_ref = true;
    }
    $name = $this->identifier();
    $parameters = $this->_parameters();

    $body = $this->startsStmt() ? $this->_innerStmt() : NULL;

    // TODO: Return method declaration with modifiers
    return new DefStmt($name, $by_ref, $body, $parameters);
  }

  private function _optMethodModifiers()
  {
    while ($this->isMethodModifier()) {
      $mod = $this->lookahead->lexeme;
      $this->consume();
      yield $mod;
    }
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

    return new Param($name, $by_ref, $ellipsis);
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

    $this->match('[');
    $body = iterator_to_array($this->_classStmtList());
    $this->match(']');

    return [
      'type'       => $type,
      'name'       => $name,
      'extends'    => $extends,
      'implements' => $implements,
      'body'       => $body
    ];
  }

  private function _try()
  {
    $this->match(Tag::T_TRY);
    $try = $this->_innerStmt();
    $rescues = iterator_to_array($this->_rescues());
    $finally = NULL;
    if ($this->is(Tag::T_FINALLY)) {
      $finally = $this->_finally();
    }

    // TODO: Use objects to store'em
    return [
      'try'     => $try,
      'rescue'  => $rescues,
      'finally' => $finally
    ];
  }

  private function _rescues()
  {
    while ($this->is(Tag::T_RESCUE)) {
      $this->match(Tag::T_RESCUE);
      // TODO: Implement name as type
      $var_name = $this->identifier();
      $body = $this->_innerStmt();

      yield [
        'variable' => $var_name,
        'body'     => $body
      ];
    }
  }

  private function _finally()
  {
    $this->match(Tag::T_FINALLY);
    return $this->_innerStmt();
  }

  private function _switch()
  {
    $this->match(Tag::T_SWITCH);
    $condition = $this->_expr();

    $this->match('[');
    $cases = iterator_to_array($this->_cases());
    $this->match(']');

    return [
      "condition" => $condition,
      "cases"     => $cases
    ];
  }

  private function _cases()
  {
    while ($this->isCase()) {
      if ($this->is(Tag::T_CASE)) {
        $this->match(Tag::T_CASE);
        $when = $this->_expr();
        $then = $this->_innerStmt();

        yield [
          'is_else' => false,
          'when'    => $when,
          'then'    => $then
        ];

      } else {
        $this->match(Tag::T_ELSE);

        yield [
          'is_else' => true,
          'when'    => NULL,
          'then'    => $this->_innerStmt()
        ];
      }
    }
  }

  public function _expr()
  {
    $token = $this->consumeAndFetch();
    $prefix_operator = $this->prefixParseletForToken($token);

    if ($prefix_operator == NULL) {
      throw (new SyntaxError)
        -> expected ('expression')
        -> found    ($token)
        -> on       ($this->position())
        -> source   ($this->input);
    }

    return $prefix_operator->parse($this, $token);
  }
}
