<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class TryStmt implements Stmt
{
  public $try;
  public $rescues;
  public $finally;

  public function __construct($try, $rescues, $finally)
  {
    $this->try = $try;
    $this->rescues = $rescues;
    $this->finally = $finally;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
