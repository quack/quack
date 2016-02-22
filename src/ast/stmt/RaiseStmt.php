<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class RaiseStmt implements Stmt
{
  public $expression;

  public function __construct($expression)
  {
    $this->expression = $expression;
  }

  public function format(Parser $parser)
  {
    $string_builder = ['raise'];
    $string_builder[] = ' ';
    $string_builder[] = $this->expression->format($parser);
    $string_builder[] = PHP_EOL;
    return implode($string_builder);
  }
}
