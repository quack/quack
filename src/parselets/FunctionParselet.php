<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Parser\Grammar;
use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Ast\Expr\LambdaExpr;
use \QuackCompiler\Lexer\Token;

class FunctionParselet implements IPrefixParselet
{
  const TYPE_EXPRESSION = 0x1;
  const TYPE_STATEMENT  = 0x2;

  public function parse(Grammar $grammar, Token $token)
  {
    // TODO: implement static and use
    ($by_reference = $grammar->parser->is('*')) && $grammar->parser->consume();
    $parameters = [];
    $type = NULL;
    $value = NULL;

    $grammar->parser->match('{');

    while ($grammar->checker->startsParameter()) {
      $parameters[] = $grammar->_parameter();

      if ($grammar->parser->is(';')) {
        $grammar->parser->consume();
      } else {
        break;
      }
    }

    $grammar->parser->match('|');
    if ($grammar->parser->is('[')) {
      $type = static::TYPE_STATEMENT;
      $grammar->parser->match('[');
      $value = iterator_to_array($grammar->_innerStmtList());
      $grammar->parser->match(']');
    } else {
      $type = static::TYPE_EXPRESSION;
      $value = $grammar->_expr();
    }

    $grammar->parser->match('}');

    return new LambdaExpr($by_reference, $parameters, $type, $value);
  }
}
