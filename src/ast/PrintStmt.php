<?php

namespace UranoCompiler\Ast;

class PrintStmt extends Node implements Stmt
{
  public function __construct(Expr $expr)
  {
    parent::__construct($expr);
  }
}
