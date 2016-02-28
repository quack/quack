<?php

namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Parser\Parser;

class ArrayPairExpr implements Expr
{
  public $left;
  public $right;

  public function __construct($left, $right)
  {
    $this->left = $left;
    $this->right = $right;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
