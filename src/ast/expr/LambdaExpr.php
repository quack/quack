<?php

namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Parser\Parser;

class LambdaExpr implements Expr
{
  public $by_reference;
  public $parameters;
  public $type;
  public $body;

  public function __construct($by_reference, $parameters, $type, $body)
  {
    $this->by_reference = $by_reference;
    $this->parameters = $parameters;
    $this->type = $type;
    $this->body = $body;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
