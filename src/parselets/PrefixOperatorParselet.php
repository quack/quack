<?php

namespace UranoCompiler\Parselets;

use \UranoCompiler\Ast\Expr\PrefixExpr;
use \UranoCompiler\Lexer\Token;
use \UranoCompiler\Parser\TokenReader;

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
