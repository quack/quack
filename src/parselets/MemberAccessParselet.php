<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Parser\Precedence;
use \QuackCompiler\Parser\Grammar;
use \QuackCompiler\Parser\SyntaxError;
use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Ast\Expr\TernaryExpr;
use \QuackCompiler\Ast\Expr\NameExpr;
use \QuackCompiler\Ast\Expr\OperatorExpr;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Lexer\Tag;

class MemberAccessParselet implements IInfixParselet
{
  public function parse(Grammar $grammar, Expr $left, Token $token)
  {
    $right = $grammar->_expr(Precedence::MEMBER_ACCESS - 1);

    if (!($right instanceof NameExpr)) {
      // throw (new SyntaxError)
      //   -> expected ('name')
      //   -> found    ($grammar->parser->lookahead)
      //   -> on       ($grammar->parser->position())
      //   -> source   ($grammar->parser->input);
    }
    return new OperatorExpr($left, $token->getTag(), $right);
  }

  public function getPrecedence()
  {
    return Precedence::MEMBER_ACCESS;
  }
}
