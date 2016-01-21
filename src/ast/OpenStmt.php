<?php

namespace UranoCompiler\Ast;

class OpenStmt implements Stmt
{
  public $open;
  public $as;

  public function __construct($open, $as)
  {
    $this->open = $open;
    $this->as = $as;
  }
}
