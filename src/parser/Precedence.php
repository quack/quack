<?php

namespace QuackCompiler\Parser;

class Precedence
{
  const ASSIGNMENT         = 1;
  const PIPELINE           = 2;
  const MEMBER_ACCESS      = 3;
  const TERNARY            = 4;
  const COALESCENCE        = 5;
  const LOGICAL_OR         = 6;
  const LOGICAL_XOR        = 7;
  const LOGICAL_AND        = 8;
  const BITWISE_OR         = 9;
  const BITWISE_XOR        = 10;
  const BITWISE_AND_OR_REF = 11;
  const VALUE_COMPARATOR   = 12;
  const SIZE_COMPARATOR    = 13;
  const BITWISE_SHIFT      = 14;
  const ADDITIVE           = 15;
  const MULTIPLICATIVE     = 16;
  const PREFIX             = 17;
  const POSTFIX            = 18;
  const O_INSTANCEOF       = 19;
  const TYPE_CAST          = 20;
  const EXPONENT           = 21;
  // TODO: Define other operators, build operators table
}
