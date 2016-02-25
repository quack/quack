<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class LetStmt implements Stmt
{
  public $name;
  public $value;

  public function __construct($name, $value)
  {
    $this->name = $name;
    $this->value = $value;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
