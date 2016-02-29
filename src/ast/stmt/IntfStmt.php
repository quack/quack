<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class IntfStmt implements Stmt
{
  public $name;
  public $extends;
  public $body;

  public function __construct($name, $extends, $body)
  {
    $this->name = $name;
    $this->extends = $extends;
    $this->body = $body;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
