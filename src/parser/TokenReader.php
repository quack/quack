<?php

namespace UranoCompiler\Parser;

use \Exception;
use \UranoCompiler\Lexer\Tag;
use \UranoCompiler\Lexer\Tokenizer;

use \UranoCompiler\Ast\Ast;
use \UranoCompiler\Ast\FunctionDecl;
use \UranoCompiler\Ast\PrintStmt;
use \UranoCompiler\Ast\Expr;

class TokenReader extends Parser
{
  public $ast = [];

  public function __construct(Tokenizer $input)
  {
    parent::__construct($input);
  }

  public function ast($scope = 0, $tree = NULL)
  {
    $level = str_repeat(' ', $scope * 2);
    $tree = $tree === NULL ? $this->ast[0] : $tree;

    echo $level . get_class($tree) . PHP_EOL;

    if (isset($tree->body[0])) {
      $this->ast($scope + 1, $tree->body[0]);
    }

    if (isset($tree->value)) {
      $this->ast($scope + 1, $tree->value);
    }
  }

  public function parse()
  {
    $this->ast[] = new Ast($this->_topStmt());
  }

  private function _topStmt()
  {
    if ($this->is(Tag::T_DEF)) {
      return $this->_functionDecl();
    }

    throw (new SyntaxError)->expected('statement')->found($this->lookahead)->on([
      "line"   => $this->input->line,
      "column" => $this->input->column
    ]);
  }

  private function _functionDecl()
  {
    $symbol_table = &$this->input->getSymbolTable();

    $this->match(Tag::T_DEF);
    $by_ref = $this->opt('*');
    $name = $this->access($this->match(Tag::T_IDENT));

    $this->match('{');

    $body = [];
    if (!$this->is('}')) {
      $body = [$this->_topStmt()];
    }

    $this->match('}');

    return (new FunctionDecl($name))
      ->byRef((boolean) $by_ref)
      ->body($body);
  }

  private function access($pointer)
  {
    return $this->input->getSymbolTable()->get($pointer);
  }
}
