<?php

namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Parser\Parser;

class TernaryExpr implements Expr
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
    $string_builder = ['('];
    $string_builder[] = $this->condition->format($parser);
    $string_builder[] = ' ? ';
    $string_builder[] = $this->then->format($parser);
    $string_builder[] = ' : ';
    $string_builder[] = $this->else->format($parser);
    $string_builder[] = ')';
    return implode($string_builder);
  }

  public function python(Parser $parser)
  {
    $string_builder = ['('];
    $string_builder[] = $this->then->python($parser);
    $string_builder[] = ' if ';
    $string_builder[] = $this->condition->python($parser);
    $string_builder[] = ' else ';
    $string_builder[] = $this->else->python($parser);
    $string_builder[] = ')';
    return implode($string_builder);
  }
}
