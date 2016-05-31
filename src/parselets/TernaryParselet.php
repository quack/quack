<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Parser\Precedence;
use \QuackCompiler\Parser\Grammar;
use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Ast\Expr\TernaryExpr;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Lexer\Tag;

class TernaryParselet implements IInfixParselet
{
  public function parse(Grammar $grammar, Expr $left, Token $token)
  {
    $then = $grammar->_expr();
    $grammar->parser->match(Tag::T_ELSE);
    $else = $grammar->_expr(Precedence::TERNARY - 1);
    return new TernaryExpr($left, $then, $else);
  }

  public function getPrecedence()
  {
    return Precedence::TERNARY;
  }
}
