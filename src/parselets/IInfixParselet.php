<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parser\Grammar;

interface IInfixParselet
{
  function parse(Grammar $parser, Expr $left, Token $token);
  function getPrecedence();
}
