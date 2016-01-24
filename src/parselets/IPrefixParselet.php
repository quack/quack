<?php

namespace UranoCompiler\Parselets;

use \UranoCompiler\Lexer\Token;
use \UranoCompiler\Parser\TokenReader;

interface IPrefixParselet
{
  function parse(TokenReader $parser, Token $token);
}
