<?php

namespace UranoCompiler\Ast;

class Expr
{
  public $value;

  public function __construct($value)
  {
    $this->value = $value;
  }
}
