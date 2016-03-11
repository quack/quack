<?php

namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Parser\Parser;

class NewExpr implements Expr
{
  public $class_name;
  public $ctor_args;

  public function __construct($class_name, $ctor_args)
  {
    $this->class_name = $class_name;
    $this->ctor_args = $ctor_args;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
