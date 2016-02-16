<?php

namespace UranoCompiler\Parselets;

use \UranoCompiler\Parser\TokenReader;
use \UranoCompiler\Ast\Expr\Expr;
use \UranoCompiler\Ast\Expr\PostfixExpr;
use \UranoCompiler\Lexer\Token;

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
