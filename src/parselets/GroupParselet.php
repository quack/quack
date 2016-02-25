<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Parser\Grammar;
use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Ast\Expr\TernaryExpr;
use \QuackCompiler\Lexer\Token;

class GroupParselet implements IPrefixParselet
{
  public function parse(Grammar $grammar, Token $token)
  {
    $expr = $grammar->_expr();
    $grammar->parser->match(')');
    return $expr;
  }
}
