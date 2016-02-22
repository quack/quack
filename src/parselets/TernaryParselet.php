<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Parser\Precedence;
use \QuackCompiler\Parser\TokenReader;
use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Ast\Expr\TernaryExpr;
use \QuackCompiler\Lexer\Token;

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
