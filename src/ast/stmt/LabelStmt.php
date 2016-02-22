<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class LabelStmt implements Stmt
{
  public $label;

  public function __construct($label)
  {
    $this->label = $label;
  }

  public function format(Parser $parser)
  {
    $string_builder = [':- '];
    $string_builder[] = $this->label;
    $string_builder[] = PHP_EOL;
    return implode($string_builder);
  }
}
