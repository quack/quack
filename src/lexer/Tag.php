<?php

namespace UranoCompiler\Lexer;

use \ReflectionClass;

class Tag
{
  /* Constructions */
  const T_IDENT = 257;
  const T_INTEGER = 258;
  const T_DOUBLE = 400;
  const T_SEMANTIC_COMMENT = 259;
  const T_STRING = 260;

  /* Keywords */
  const T_TRUE = 260;
  const T_FALSE = 261;
  const T_IF = 262;
  const T_FOR = 263;
  const T_WHILE = 264;
  const T_DO = 265;
  const T_STRUCT = 266;
  const T_INIT = 267;
  const T_SELF = 268;
  const T_MODULE = 269;
  const T_CLASS = 270;
  const T_DEF = 271;
  const T_NIL = 272;
  const T_LET = 273;
  const T_CONST = 274;
  const T_GOTO = 275;
  const T_MY = 276;
  const T_OVERRIDE = 277;
  const T_PROTECTED = 278;
  const T_MODEL = 279;
  const T_WHERE = 280;
  const T_FOREACH = 281;
  const T_MATCH = 282;
  const T_STATIC = 283;
  const T_IN = 284;
  const T_PROTOCOL = 500;
  const T_FINAL = 501;
  const T_OPEN = 502;
  const T_GLOBAL = 503;
  const T_AS = 504;
  const T_TYPE = 505;
  const T_ENUM = 506;
  const T_WITH = 507;

  const T_CONTINUE = 508;
  const T_SWITCH = 509;
  const T_BREAK = 510;
  const T_AND = 511;
  const T_OR = 512;
  const T_XOR = 513;
  const T_INSTANCEOF = 514;
  const T_EXTENSION = 515;

  const T_TRY = 515;
  const T_RESCUE = 516;
  const T_FINALLY = 517;
  const T_RAISE = 518;
  const T_TYPE_CALLABLE = 519;
  const T_ELIF = 520;
  const T_ELSE = 521;
  const T_CASE = 522;
  const T_DECLARE = 523;
  const T_YIELD = 524;
  const T_SUPER = 525;
  const T_PARTIAL = 526;
  const T_IS = 527;

  const T_TYPE_INT = 285;
  const T_TYPE_STRING = 286;
  const T_TYPE_BOOL = 287;
  const T_TYPE_ARRAY = 288;
  const T_TYPE_RESOURCE = 289;
  const T_TYPE_OBJECT = 290;
  const T_TYPE_DOUBLE = 291;

  static function getName($x)
  {
    return array_search($x, (new ReflectionClass(__CLASS__))->getConstants());
  }
}
