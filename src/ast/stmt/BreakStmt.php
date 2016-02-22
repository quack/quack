<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class BreakStmt implements Stmt
{
  public $label;

  public function __construct($label = NULL)
  {
    $this->label = $label;
  }

  public function format(Parser $parser)
  {
    $string_builder = ['break'];

    if (!is_null($this->label)) {
      $string_builder[] = ' ';
      $string_builder[] = $this->label->format($parser);
    }

    $string_builder[] = PHP_EOL;

    return implode($string_builder);
  }
}
