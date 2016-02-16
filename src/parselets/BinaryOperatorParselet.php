<?php

namespace UranoCompiler\Parselets;

use \UranoCompiler\Parser\TokenReader;
use \UranoCompiler\Ast\Expr\Expr;
use \UranoCompiler\Ast\Expr\OperatorExpr;
use \UranoCompiler\Lexer\Token;

class BinaryOperatorParselet implements IInfixParselet
{
  public function parse(TokenReader $parser, Expr $left, Token $token)
  {
    $right = $parser->_expr();
    return new OperatorExpr($left, $token->getTag(), $right);
  }
}
