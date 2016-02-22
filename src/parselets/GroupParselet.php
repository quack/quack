<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Parser\TokenReader;
use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Ast\Expr\TernaryExpr;
use \QuackCompiler\Lexer\Token;

class GroupParselet implements IPrefixParselet
{
  public function parse(TokenReader $parser, Token $token)
  {
    $expr = $parser->_expr();
    $parser->match(')');
    return $expr;
  }
}
