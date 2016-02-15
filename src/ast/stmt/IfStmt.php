<?php

namespace UranoCompiler\Ast\Stmt;

use \UranoCompiler\Parser\Parser;

class IfStmt implements Stmt
{
  public $condition;
  public $body;
  public $elif;
  public $else;

  public function __construct($condition, $body, $elif, $else)
  {
    $this->condition = $condition;
    $this->body = $body;
    $this->elif = $elif;
    $this->else = $else;
  }

  public function format(Parser $parser)
  {
    return 'TODO';
  }
}
