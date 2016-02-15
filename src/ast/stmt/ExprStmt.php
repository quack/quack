<?php

namespace UranoCompiler\Ast\Stmt;

use \UranoCompiler\Parser\Parser;

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
}
