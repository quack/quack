<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parser\TokenReader;

interface IPrefixParselet
{
  function parse(TokenReader $parser, Token $token);
}
