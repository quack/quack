<?php

namespace UranoCompiler\Parselets;

use \UranoCompiler\Ast\Expr\PrefixExpr;
use \UranoCompiler\Lexer\Token;
use \UranoCompiler\Parser\TokenReader;

class PrefixOperatorParselet implements IPrefixParselet
{
  public function parse(TokenReader $parser, Token $token)
  {
    $operand = $parser->_expr();
    return new PrefixExpr($token, $operand);
  }
}
