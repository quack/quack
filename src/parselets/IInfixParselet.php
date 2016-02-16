<?php

namespace UranoCompiler\Parselets;

use \UranoCompiler\Ast\Expr\Expr;
use \UranoCompiler\Lexer\Token;
use \UranoCompiler\Parser\TokenReader;

interface IInfixParselet
{
  function parse(TokenReader $parser, Expr $left, Token $token);
  // TODO: Not now, but after. function getPrecedence();
}
