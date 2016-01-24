<?php

namespace UranoCompiler\Parselets;

use \UranoCompiler\Lexer\Token;
use \UranoCompiler\Parser\TokenReader;

interface IInfixParselet
{
  function parse(TokenReader $parser, Expr $left, Token $token);
  function getPrecedence();
}
