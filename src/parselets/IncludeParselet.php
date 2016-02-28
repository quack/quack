<?php

namespace QuackCompiler\Parselets;

use \QuackCompiler\Ast\Expr\IncludeExpr;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parser\Grammar;

class IncludeParselet implements IPrefixParselet
{
  const TYPE_REQUIRE = 0x1;
  const TYPE_INCLUDE = 0x2;

  public function parse(Grammar $grammar, Token $token)
  {
    $type = $token->getTag() === Tag::T_REQUIRE
      ? static::TYPE_REQUIRE
      : static::TYPE_INCLUDE;
    $is_once = $grammar->parser->is(Tag::T_ONCE) && $grammar->parser->consume();
    $file = $grammar->_expr();

    return new IncludeExpr($type, $is_once, $file);
  }
}
