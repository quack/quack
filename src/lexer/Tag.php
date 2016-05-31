<?php

namespace QuackCompiler\Lexer;

use \ReflectionClass;

class Tag
{
  /* Constructions */
  const T_IDENT = 257;
  const T_INTEGER = 258;
  const T_DOUBLE = 400;
  const T_SEMANTIC_COMMENT = 259;
  const T_STRING = 600;
  const T_PARAM = 1000;

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
  const T_EXTENSION = 800;
  const T_PRINT = 801;
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
  const T_OUT = 528;
  const T_DERIVING = 529;
  const T_LETF = 530;
  const T_TYPE_INT = 285;
  const T_TYPE_STRING = 286;
  const T_TYPE_BOOL = 287;
  const T_TYPE_ARRAY = 288;
  const T_TYPE_RESOURCE = 289;
  const T_TYPE_OBJECT = 290;
  const T_TYPE_DOUBLE = 291;
  const T_MOD = 292;
  const T_NOT = 293;
  const T_FN = 294;
  const T_INCLUDE = 295;
  const T_REQUIRE = 296;
  const T_ONCE = 297;
  const T_PIECE = 298;
  const T_INTF = 299;
  const T_THEN = 300;

  /* Operators */

  # <
  const T_LESSER              = 1000; # <
  const T_RETURN              = 1001; # <<<
  const T_BITWISE_SHIFT_LEFT  = 1002; # <<
  const T_DIFFERENT           = 1003; # <>
  const T_LESSER_OR_EQUAL     = 1004; # <=

  # >
  const T_GREATER             = 1005; # >
  const T_ECHO                = 1006; # >>>
  const T_GREATER_OR_EQUAL    = 1007; # >=
  const T_BITWISE_SHIFT_RIGHT = 1008; # >>>

  # :
  const T_COLON               = 1009; # :
  const T_ASSIGN              = 1010; # :-

  # -
  const T_PLUS                = 1011; # +
  const T_CONCAT_LIST         = 1012; # +++
  const T_CONCAT              = 1013; # ++

  # *
  const T_STAR                = 1014; # *
  const T_POW                 = 1015; # **

  # =
  const T_EQUAL               = 1016; # =
  const T_REGEX_EQUAL         = 1017; # =~

  # |
  const T_BITWISE_OR          = 1018; # |
  const T_PIPELINE            = 1019; # |>

  # ^
  const T_CIRCUNFLEX          = 1020; # ^
  const T_CLONE               = 1021; # ^^

  # &
  const T_BITWISE_AND         = 1022; # &
  const T_PARAMETERLESS_FN    = 1023; # &{ expr }
  const T_PARTIAL_FN          = 1024; # &( op expr )

  # .
  const T_DOT                 = 1025; # .
  const T_ELLIPSIS            = 1026; # ...

  # ?
  const T_SIMPLE_IF           = 1027; # ?
  const T_SIMPLE_TERNARY      = 1028; # ?:
  const T_NULL_COALESCENCE    = 1029; # ??

  # Single char operators
  const T_NEW                 = 1030; # #
  const T_AT                  = 1031; # @
  const T_BANG                = 1032; # !
  const T_MINUS               = 1033; # -
  const T_BITWISE_NOT         = 1034; # ~
  # @{link T_INSTANCEOF}
  # @{link T_AND}
  # @{link T_OR}
  # @{link T_XOR}
  # @{link T_NOT}
  # @{link T_MOD}

  static function getPunctuator($code)
  {
    $op_table = static::getOpTable();

    if (in_array($code, $op_table, true)) {
      if (is_string($code)) {
        return $code;
      }

      switch ($code) {
        case Tag::T_INSTANCEOF:
          return 'instanceof';
        case Tag::T_AND:
          return 'and';
        case Tag::T_OR:
          return 'or';
        case Tag::T_XOR:
          return 'xor';
        case Tag::T_NOT:
          return 'not';
        case Tag::T_MOD:
          return 'mod';
      }
    }

    return NULL;
  }

  // TODO: Separate operators that can be used as the start of an expression
  // from the others
  static function getOpTable() {
    return [
      Tag::T_LESSER              => '<',
      Tag::T_RETURN              => '<<<',
      Tag::T_BITWISE_SHIFT_LEFT  => '<<',
      Tag::T_DIFFERENT           => '<>',
      Tag::T_LESSER_OR_EQUAL     => '<=',
      Tag::T_GREATER             => '>',
      Tag::T_ECHO                => '>>>',
      Tag::T_GREATER_OR_EQUAL    => '>=',
      Tag::T_BITWISE_SHIFT_RIGHT => '>>',
      Tag::T_COLON               => ':',
      Tag::T_ASSIGN              => ':-',
      Tag::T_PLUS                => '+',
      Tag::T_CONCAT_LIST         => '+++',
      Tag::T_CONCAT              => '++',
      Tag::T_STAR                => '*',
      Tag::T_POW                 => '**',
      Tag::T_EQUAL               => '=',
      Tag::T_REGEX_EQUAL         => '=~',
      Tag::T_BITWISE_OR          => '|',
      Tag::T_PIPELINE            => '|>',
      Tag::T_CIRCUNFLEX          => '^',
      Tag::T_CLONE               => '^^',
      Tag::T_BITWISE_AND         => '&',
      Tag::T_PARAMETERLESS_FN    => '&{',
      Tag::T_PARTIAL_FN          => '&(',
      Tag::T_DOT                 => '.',
      Tag::T_ELLIPSIS            => '...',
      Tag::T_SIMPLE_IF           => '?',
      Tag::T_SIMPLE_TERNARY      => '?:',
      Tag::T_NULL_COALESCENCE    => '??',
      Tag::T_NEW                 => 'new',
      Tag::T_AT                  => '@',
      Tag::T_BANG                => '!',
      Tag::T_MINUS               => '-',
      Tag::T_BITWISE_NOT         => '~',
      Tag::T_NEW                 => '#',
      Tag::T_INSTANCEOF          => Tag::T_INSTANCEOF,
      Tag::T_AND                 => Tag::T_AND,
      Tag::T_OR                  => Tag::T_OR,
      Tag::T_XOR                 => Tag::T_XOR,
      Tag::T_NOT                 => Tag::T_NOT,
      Tag::T_MOD                 => Tag::T_MOD
    ];
  }

  static function getName($x)
  {
    return array_search($x, (new ReflectionClass(__CLASS__))->getConstants());
  }
}
