<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parser\Grammar;

interface IPrefixParselet
{
  function parse(Grammar $parser, Token $token);
}
