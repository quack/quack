<?php

namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Parser\Parser;

class NumberExpr implements Expr
{
  private $token;

  public function __construct($token)
  {
    $this->token = $token;
  }

  public function format(Parser $parser)
  {
    return $parser->resolveScope($this->token->getPointer());
  }

  public function python(Parser $parser)
  {
    return $this->format($parser);
  }
}
