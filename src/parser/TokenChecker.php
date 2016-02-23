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

  function startsTopStmt()
  {
    return $this->startsStmt()
        || $this->startsClassDeclStmt()
        || $this->parser->is(Tag::T_DEF)
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
    return $this->parser->is(Tag::T_IF)
        || $this->parser->is(Tag::T_WHILE)
        || $this->parser->is(Tag::T_FOR)
        || $this->parser->is(Tag::T_FOREACH)
        || $this->parser->is(Tag::T_MATCH)
        || $this->parser->is(Tag::T_BREAK)
        || $this->parser->is(Tag::T_CONTINUE)
        || $this->parser->is(Tag::T_YIELD)
        || $this->parser->is(Tag::T_GLOBAL)
        || $this->parser->is(Tag::T_STATIC)
        || $this->parser->is(Tag::T_RAISE)
        || $this->parser->is(Tag::T_PRINT)
        || $this->parser->is(Tag::T_PRINT)
        || $this->parser->is('<<<')
        || $this->parser->is('>>>')
        || $this->parser->is('[')
        || $this->startsExpr();
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

  function isMethodModifier()
  {
    return $this->parser->is(Tag::T_MY)
        || $this->parser->is(Tag::T_PROTECTED)
        || $this->parser->is(Tag::T_STATIC)
        || $this->parser->is(Tag::T_MODEL)
        || $this->parser->is(Tag::T_FINAL);
  }

  function startsClassStmt()
  {
    return $this->parser->is(Tag::T_DEF)
        || $this->isMethodModifier();
  }

  function isEoF()
  {
    return $this->parser->lookahead->getTag() === 0;
  }
}
