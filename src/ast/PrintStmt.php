<?php

namespace UranoCompiler\Ast;

class PrintStmt extends Node
{
  public function __construct(Expr $expr)
  {
    parent::__construct($expr);
  }
}
