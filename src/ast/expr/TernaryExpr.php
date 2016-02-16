<?php

namespace UranoCompiler\Ast\Expr;

use \UranoCompiler\Parser\Parser;

class TernaryExpr
{
  public $condition;
  public $then;
  public $else;

  public function __construct($condition, $then, $else)
  {
    $this->condition = $condition;
    $this->then = $then;
    $this->else = $else;
  }

  public function format(Parser $parser)
  {
    $string_builder = [$this->condition->format($parser)];
    $string_builder[] = ' ? ';
    $string_builder[] = $this->then->format($parser);
    $string_builder[] = ' : ';
    $string_builder[] = $this->else->format($parser);
    return implode($string_builder);
  }
}
