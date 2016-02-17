<?php

namespace UranoCompiler\Parser;

class Precedence
{
  const ASSIGNMENT         = 1;
  const TERNARY            = 2;
  const LOGICAL_OR         = 3;
  const LOGICAL_XOR        = 4;
  const LOGICAL_AND        = 5;
  const BITWISE_OR         = 6;
  const BITWISE_XOR        = 7;
  const BITWISE_AND_OR_REF = 8;
  const VALUE_COMPARATOR   = 9;
  const SIZE_COMPARATOR    = 10;
  const BITWISE_SHIFT      = 11;
  const ADDITIVE           = 12;
  const MULTIPLICATIVE     = 13;
  const PREFIX             = 14;
  const POSTFIX            = 15;
  const O_INSTANCEOF       = 16;
  const TYPE_CAST          = 17;
  const EXPONENT           = 18;
  // TODO: Define other operators
}
