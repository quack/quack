<?php

namespace UranoCompiler\Ast\Expr;

use \UranoCompiler\Parser\Parser;

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
}
