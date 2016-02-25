<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class RescueStmt implements Stmt
{
  public $exception_class;
  public $variable;
  public $body;

  public function __construct($exception_class, $variable, $body)
  {
    $this->exception_class = $exception_class;
    $this->variable = $variable;
    $this->body = $body;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
