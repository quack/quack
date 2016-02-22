<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Ast\Expr\PrefixExpr;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parser\TokenReader;

class PrefixOperatorParselet implements IPrefixParselet
{
  public $precedence;

  public function __construct($precedence)
  {
    $this->precedence = $precedence;
  }

  public function parse(TokenReader $parser, Token $token)
  {
    $operand = $parser->_expr($this->precedence);
    return new PrefixExpr($token, $operand);
  }

  public function getPrecedence()
  {
    return $this->precedence;
  }
}
