<?php

namespace QuackCompiler\Parser;

class Precedence
{
  const ASSIGNMENT         = 1;
  const PIPELINE           = 2;
  const TERNARY            = 3;
  const LOGICAL_OR         = 4;
  const LOGICAL_XOR        = 5;
  const LOGICAL_AND        = 6;
  const BITWISE_OR         = 7;
  const BITWISE_XOR        = 8;
  const BITWISE_AND_OR_REF = 9;
  const VALUE_COMPARATOR   = 10;
  const SIZE_COMPARATOR    = 11;
  const BITWISE_SHIFT      = 12;
  const ADDITIVE           = 13;
  const MULTIPLICATIVE     = 14;
  const PREFIX             = 15;
  const POSTFIX            = 16;
  const O_INSTANCEOF       = 17;
  const TYPE_CAST          = 18;
  const EXPONENT           = 19;
  // TODO: Define other operators, build operators table
}
