<?php

namespace QuackCompiler\Lexer;

class SymbolDecypher
{
  public static function add($x, $y) { return $x + $y; }

  public static function __callStatic($method, $args)
  {
    $context = &$args[0];

    switch ($method) {
      case '<':
        return static::tryMatch($context, ['<<<', '<<', '<>', '<=']);
      case '>':
        return static::tryMatch($context, ['>>>', '>=', '>>']);
      case ':':
        return static::tryMatch($context, [':-', ':?']);
      case '+':
        return static::tryMatch($context, ['+++', '++']);
      case '?':
        return static::tryMatch($context, ['?:', '??']);
      case '*':
        return static::tryMatch($context, ['**']);
      case '=':
        return static::tryMatch($context, ['=~']);
      case '|':
        return static::tryMatch($context, ['|>']);
      case '^':
        return static::tryMatch($context, ['^^']);
      case '&':
        if (ctype_digit($context->preview())) {
          $context->consume();
          $param_index = $context->digit()->getPointer();
          return new Token(Tag::T_PARAM, $param_index);
        }

        return static::tryMatch($context, ['&{', '&(']);
      case '.':
        return static::tryMatch($context, ['...']);
      default:
        return static::fetch($context, $context->peek);
    }
  }

  private static function tryMatch(&$context, $operator_list)
  {
    foreach ($operator_list as $operator) {
      if ($context->matches($operator)) {
        return static::fetch($context, $operator);
      }
    }

    return static::fetch($context, $context->peek);
  }

  private static function fetch($context, $symbol)
  {
    $size = strlen($symbol);
    $context->consume($size);
    $context->column += $size;
    return new Token($symbol);
  }
}
