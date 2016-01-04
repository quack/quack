<?php

namespace UranoCompiler\Lexer;

class SymbolDecypher
{
  public static function __callStatic($method, $args)
  {
    $context = $args[0];

    switch ($method) {
      case '<':
        return static::tryMatch(['<<<', '<<=', '<<', '<>', '<='], $context);
      case '>':
        return static::tryMatch(['>>>', '>>=', '>=', '>>'], $context);
      case ':':
        return static::tryMatch(['::', ':='], $context);
      case '-':
        return static::tryMatch(['->', '-='], $context);
      case '+':
        return static::tryMatch(['+=', '++=', '+++', '++'], $context);
      case '*':
        return static::tryMatch(['*=', '**'], $context);
      case '/':
        return static::tryMatch(['/='], $context);
      case '=':
        return static::tryMatch(['=~', '=='], $context);
      case '|':
        return static::tryMatch(['|>'], $context);
      case '^':
        return static::tryMatch(['^^'], $context);
      default:
        return static::fetch($context, $context->peek);
    }
  }

  private static function tryMatch($operator_list, $context)
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
