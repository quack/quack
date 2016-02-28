<?php

namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Parser\Parser;

class ArrayExpr implements Expr
{
  public $items;

  public function __construct($items)
  {
    $this->items = $items;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
