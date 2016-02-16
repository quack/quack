<?php

namespace UranoCompiler\Parser;

class Precedence
{
  const TERNARY = 2;
  const ADDITIVE = 3;
  const MULTIPLICATIVE = 4;
  const EXPONENT = 5;
  const PREFIX = 666;
  const POSTFIX = 7;
}
