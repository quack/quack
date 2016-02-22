<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class ExprStmt implements Stmt
{
  public $expression;

  public function __construct($expression)
  {
    $this->expression = $expression;
  }

  public function format(Parser $parser)
  {
    $string_builder = [$this->expression->format($parser)];
    $string_builder[] = ";\n";
    return implode($string_builder);
  }

  public function python(Parser $parser)
  {
    $string_builder = [$this->expression->python($parser)];
    $string_builder[] = "\n";
    return implode($string_builder);
  }
}
