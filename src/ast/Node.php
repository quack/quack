<?php

namespace UranoCompiler\Ast;

class Node
{
  public $value;

  public function __construct($value)
  {
    $this->value = $value;
  }
}
