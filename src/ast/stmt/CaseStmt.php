<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class CaseStmt implements Stmt
{
  public $value;
  public $body;
  public $is_else;

  public function __construct($value, $body, $is_else = false)
  {
    $this->value = $value;
    $this->body = $body;
    $this->is_else = $is_else;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
