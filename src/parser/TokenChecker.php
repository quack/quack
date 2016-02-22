<?php

namespace QuackCompiler\Parser;

use \QuackCompiler\Lexer\Tag;

class TokenChecker
{
  private $parser;

  function __construct(TokenReader $parser)
  {
    $this->parser = $parser;
  }

  function isAny($options)
  {
    foreach ($options as $option) {
      if ($this->parser->is($option)) {
        return true;
      }
    }
    return false;
  }

  function startsTopStmt()
  {
    return $this->startsStmt()
        || $this->parser->is(Tag::T_DEF)
        || $this->startsClassDeclStmt()
        || $this->parser->is(Tag::T_MODULE)
        || $this->parser->is(Tag::T_OPEN)
        || $this->parser->is(Tag::T_CONST);
  }

  function startsInnerStmt()
  {
    return $this->startsStmt()
        || $this->parser->is(Tag::T_DEF)
        || $this->startsClassDeclStmt();
  }

  function startsStmt()
  {
    return $this->isAny([
      Tag::T_IF, Tag::T_WHILE, Tag::T_FOR, Tag::T_FOREACH, Tag::T_MATCH,
      Tag::T_BREAK, Tag::T_CONTINUE, Tag::T_YIELD, Tag::T_GLOBAL, Tag::T_STATIC,
      Tag::T_RAISE, Tag::T_PRINT, Tag::T_TRY, '<<<', '>>>'
    ]) || $this->startsExpr();
  }

  function startsExpr()
  {
    return $this->parser->is(Tag::T_INTEGER)
        || $this->parser->is(Tag::T_DOUBLE)
        || $this->parser->isOperator()
        || $this->parser->is('(');
  }

  function startsClassDeclStmt()
  {
    return $this->parser->is(Tag::T_FINAL)
        || $this->parser->is(Tag::T_MODEL)
        || $this->parser->is(Tag::T_CLASS);
  }

  function startsParameter()
  {
    return $this->is('...')
        || $this->is('*')
        || $this->is(Tag::T_IDENT);
  }

  function startsCase()
  {
    return $this->is(Tag::T_CASE)
        || $this->is(Tag::T_ELSE);
  }
}
