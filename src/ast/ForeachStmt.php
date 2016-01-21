<?php

namespace UranoCompiler\Ast;

class ForeachStmt implements Stmt
{
  public $by_ref;
  public $alias;
  public $generator;
  public $body;

  public function __construct($by_ref, $alias, $generator, $body)
  {
    $this->by_ref = $by_ref;
    $this->alias = $alias;
    $this->generator = $generator;
    $this->body = $body;
  }
}
