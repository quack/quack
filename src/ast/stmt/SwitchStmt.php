<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class SwitchStmt implements Stmt
{
  public $value;
  public $cases;

  public function __construct($value, $cases)
  {
    $this->value = $value;
    $this->cases = $cases;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
