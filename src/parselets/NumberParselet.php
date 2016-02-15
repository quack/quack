<?php

namespace UranoCompiler\Parselets;

use \UranoCompiler\Ast\Expr\NumberExpr;
use \UranoCompiler\Lexer\Token;
use \UranoCompiler\Parser\TokenReader;

class NumberParselet implements IPrefixParselet
{
  public function parse(TokenReader $parser, Token $token)
  {
    return new NumberExpr($token);
  }
}
