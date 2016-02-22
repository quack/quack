<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Ast\Expr\NumberExpr;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parser\TokenReader;

class NumberParselet implements IPrefixParselet
{
  public function parse(TokenReader $parser, Token $token)
  {
    return new NumberExpr($token);
  }
}
