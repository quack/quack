<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class GlobalStmt implements Stmt
{
  public $name;

  public function __construct($name)
  {
    $this->name = $name;
  }

  public function format(Parser $parser)
  {
    $string_builder = ['global '];
    $string_builder[] = $this->name;
    $string_builder[] = PHP_EOL;
    return implode($string_builder);
  }
}
