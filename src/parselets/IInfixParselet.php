<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parser\TokenReader;

interface IInfixParselet
{
  function parse(TokenReader $parser, Expr $left, Token $token);
  function getPrecedence();
}
