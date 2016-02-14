<?php

namespace UranoCompiler\Ast;

use \UranoCompiler\Lexer\Token;

class PrefixExpression extends Expr
{
  private $operator;
  private $right;

  public function __construct(Token $operator, $right)
  {
    $this->operator = $operator->getTag();
    $this->right = $right;
  }
}
