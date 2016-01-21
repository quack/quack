<?php

namespace UranoCompiler\Ast;

class IfStmt implements Stmt
{
  public $condition;
  public $body;

  public function __construct(Expr $condition, Stmt $body)
  {
    $this->condition = $condition;
    $this->body = $body;
  }
}
