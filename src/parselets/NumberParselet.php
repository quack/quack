<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Ast\Expr\NumberExpr;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parser\Grammar;

class NumberParselet implements IPrefixParselet
{
  public function parse(Grammar $parser, Token $token)
  {
    return new NumberExpr($token);
  }
}
