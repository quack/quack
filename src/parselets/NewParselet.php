<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Parser\Grammar;
use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Ast\Expr\NewExpr;
use \QuackCompiler\Lexer\Token;

class NewParselet implements IPrefixParselet
{
  public function parse(Grammar $grammar, Token $token)
  {
    $class_name = $grammar->qualifiedName();
    $ctor_args = [];

    if ($grammar->parser->is('[')) {
      $grammar->parser->consume();

      while ($grammar->checker->startsExpr()) {
        $ctor_args[] = $grammar->_expr();

        if (!$grammar->parser->is(']')) {
          $grammar->parser->match(';');
        }
      }

      $grammar->parser->match(']');
    } else {
      $grammar->parser->match('!');
    }

    return new NewExpr($class_name, $ctor_args);
  }
}
