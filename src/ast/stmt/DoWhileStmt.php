<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class DoWhileStmt implements Stmt
{
  public $condition;
  public $body;

  public function __construct($condition, $body)
  {
    $this->condition = $condition;
    $this->body = $body;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
