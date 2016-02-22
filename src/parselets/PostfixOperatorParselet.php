<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Parser\TokenReader;
use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Ast\Expr\PostfixExpr;
use \QuackCompiler\Lexer\Token;

class PostfixOperatorParselet implements IInfixParselet
{
  public $precedence;

  public function __construct($precedence)
  {
    $this->precedence = $precedence;
  }

  public function parse(TokenReader $parser, Expr $left, Token $token)
  {
    return new PostfixExpr($left, $token->getTag());
  }

  public function getPrecedence()
  {
    return $this->precedence;
  }
}
