<?php

namespace UranoCompiler\Ast;

use \UranoCompiler\Parser\Parser;

class NumberExpression extends Expr
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
