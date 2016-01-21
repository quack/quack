<?php

namespace UranoCompiler\Ast;

class IfStmt implements Stmt
{
  public $condition;
  public $body;
  public $elif;
  public $else;

  public function __construct(Expr $condition, Stmt $body, array $elif, Stmt $else = NULL)
  {
    $this->condition = $condition;
    $this->body = $body;
    $this->elif = $elif;
    $this->else = $else;
  }
}
