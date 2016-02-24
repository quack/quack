<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class ElifStmt implements Stmt
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
    throw new \Exception('TODO. Not implemented');
  }
}
