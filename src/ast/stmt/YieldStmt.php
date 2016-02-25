<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class YieldStmt implements Stmt
{
  public $expression;

  public function __construct($expression = NULL)
  {
    $this->expression = $expression;
  }

  public function format(Parser $parser)
  {
    $string_builder = ['yield '];

    if (!is_null($this->expression)) {
      $string_builder[] = ' ';
      $string_builder[] = $this->expression->format($parser);
      $string_builder[] = PHP_EOL;
    }

    return implode($string_builder);
  }

  public function python(Parser $parser)
  {
    $string_builder = ['yield from '];

    if (!is_null($this->expression)) {
      $string_builder[] = ' ';
      $string_builder[] = $this->expression->python($parser);
      $string_builder[] = PHP_EOL;
    }

    return implode($string_builder);
  }
}
