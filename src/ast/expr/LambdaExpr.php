<?php

namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Parser\Parser;

class LambdaExpr implements Expr
{
  public $by_reference;
  public $parameters;
  public $type;
  public $body;
  public $is_static;
  public $lexical_vars;

  public function __construct($by_reference, $parameters, $type, $body, $is_static, $lexical_vars)
  {
    $this->by_reference = $by_reference;
    $this->parameters = $parameters;
    $this->type = $type;
    $this->body = $body;
    $this->is_static = $is_static;
    $this->lexical_vars = $lexical_vars;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
