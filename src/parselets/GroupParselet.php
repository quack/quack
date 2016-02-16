<?php

namespace UranoCompiler\Parselets;

use \UranoCompiler\Parser\TokenReader;
use \UranoCompiler\Ast\Expr\Expr;
use \UranoCompiler\Ast\Expr\TernaryExpr;
use \UranoCompiler\Lexer\Token;

class GroupParselet implements IPrefixParselet
{
  public function parse(TokenReader $parser, Token $token)
  {
    // TODO: Implement parenthesis on code formatter
    $expr = $parser->_expr();
    $parser->match(')');
    return $expr;
  }
}
