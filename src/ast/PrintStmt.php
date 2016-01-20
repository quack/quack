<?php

namespace UranoCompiler\Ast;

class PrintStmt
{
  public $value;

  public function __construct(Expr $expr)
  {
    $this->value = $expr;
  }
}
