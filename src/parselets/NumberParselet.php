<?php

namespace UranoCompiler\Parselets;

use \UranoCompiler\Lexer\Token;
use \UranoCompiler\Parser\TokenReader;

class NumberParselet implements IPrefixParselet
{
  public function parse(TokenReader $parser, Token $token)
  {
    return [
      "type"  => "NumberExpr",
      "value" => $token
    ];
  }
}
