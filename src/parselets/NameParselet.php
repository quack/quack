<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Ast\Expr\NameExpr;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parser\Grammar;

class NameParselet implements IPrefixParselet
{
  public function parse(Grammar $parser, Token $token)
  {
    return new NameExpr($token);
  }
}
