<?php

namespace UranoCompiler\Ast\Expr;

use \UranoCompiler\Parser\Parser;

class OperatorExpr implements Expr
{
  public $left;
  public $operator;
  public $right;

  public function __construct(Expr $left, $operator, Expr $right)
  {
    $this->left = $left;
    $this->operator = $operator;
    $this->right = $right;
  }

  public function format(Parser $parser)
  {
    // TODO: We need to represent this code as string.
    // This area is also used to beautify the code.
    throw new \Exception('TODO');
  }
}
