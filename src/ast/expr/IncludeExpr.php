<?php

namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Parser\Parser;

class IncludeExpr implements Expr
{
  public $type;
  public $is_once;
  public $file;

  public function __construct($type, $is_once, $file)
  {
    $this->type = $type;
    $this->is_once = $is_once;
    $this->file = $file;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
