<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class PropertyStmt implements Stmt
{
  public $name;
  public $value;
  public $modifiers;

  public function __construct($name, $value, $modifiers = [])
  {
    $this->name = $name;
    $this->value = $value;
    $this->modifiers = $modifiers;
  }

  public function format(Parser $parser)
  {
    throw new TodoException;
  }
}
