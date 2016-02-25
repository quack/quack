<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class StructStmt implements Stmt
{
  public $name;
  public $interfaces;
  public $body;

  public function __construct($name, $interfaces, $body)
  {
    $this->name = $name;
    $this->interfaces = $interfaces;
    $this->body = $body;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
