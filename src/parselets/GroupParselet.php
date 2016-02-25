<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Parser\Grammar;
use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Ast\Expr\TernaryExpr;
use \QuackCompiler\Lexer\Token;

class GroupParselet implements IPrefixParselet
{
  public function parse(Grammar $parser, Token $token)
  {
    $expr = $parser->_expr();
    $parser->match(')');
    return $expr;
  }
}
