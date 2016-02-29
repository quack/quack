<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class PieceStmt implements Stmt
{
  public $name;
  public $body;

  public function __construct($name, $body)
  {
    $this->name = $name;
    $this->body = $body;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
