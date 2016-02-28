<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Parser\Grammar;
use \QuackCompiler\Ast\Expr\ArrayExpr;
use \QuackCompiler\Lexer\Token;

class ArrayParselet implements IPrefixParselet
{
  public function parse(Grammar $grammar, Token $token)
  {
    $items = iterator_to_array($grammar->_arrayPairList());
    $grammar->parser->match('}');
    return new ArrayExpr($items);
  }
}
