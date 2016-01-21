<?php

namespace UranoCompiler\Ast;

class IfStmt implements Stmt
{
  public $condition;
  public $body;
  public $else;

  public function __construct(Expr $condition, Stmt $body, Stmt $else = NULL)
  {
    $this->condition = $condition;
    $this->body = $body;
    $this->else = $else;
  }
}
