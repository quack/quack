<?php

namespace QuackCompiler\Parser;

class Precedence
{
  const ASSIGNMENT         = 1;
  const PIPELINE           = 2;
  const TERNARY            = 3;
  const COALESCENCE        = 4;
  const LOGICAL_OR         = 5;
  const LOGICAL_XOR        = 6;
  const LOGICAL_AND        = 7;
  const BITWISE_OR         = 8;
  const BITWISE_XOR        = 9;
  const BITWISE_AND_OR_REF = 10;
  const VALUE_COMPARATOR   = 11;
  const SIZE_COMPARATOR    = 12;
  const BITWISE_SHIFT      = 13;
  const ADDITIVE           = 14;
  const MULTIPLICATIVE     = 15;
  const PREFIX             = 16;
  const POSTFIX            = 17;
  const O_INSTANCEOF       = 18;
  const TYPE_CAST          = 19;
  const EXPONENT           = 20;
  // TODO: Define other operators, build operators table
}
