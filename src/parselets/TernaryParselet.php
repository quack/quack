<?php

namespace UranoCompiler\Parselets;

use \UranoCompiler\Parser\Precedence;
use \UranoCompiler\Parser\TokenReader;
use \UranoCompiler\Ast\Expr\Expr;
use \UranoCompiler\Ast\Expr\TernaryExpr;
use \UranoCompiler\Lexer\Token;

class TernaryParselet implements IInfixParselet
{
  public function parse(TokenReader $parser, Expr $left, Token $token)
  {
    $then = $parser->_expr();
    $parser->match(':');
    $else = $parser->_expr(Precedence::TERNARY - 1);
    return new TernaryExpr($left, $then, $else);
  }

  public function getPrecedence()
  {
    return Precedence::TERNARY;
  }
}
