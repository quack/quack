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
        || $this->parser->is(Tag::T_STRUCT)
        || $this->parser->is(Tag::T_FN)
        || $this->parser->is(Tag::T_MODULE)
        || $this->parser->is(Tag::T_OPEN)
        || $this->parser->is(Tag::T_CONST);
  }

  function startsInnerStmt()
  {
    return $this->startsStmt()
        || $this->parser->is(Tag::T_FN)
        || $this->startsClassDeclStmt();
  }

  function startsStmt()
  {
    return $this->parser->is(Tag::T_IF)       // Done
        || $this->parser->is(Tag::T_LET)      // Done
        || $this->parser->is(Tag::T_WHILE)    // Partially done
        || $this->parser->is(Tag::T_DO)       // Done
        || $this->parser->is(Tag::T_FOR)      //
        || $this->parser->is(Tag::T_FOREACH)  // Done
        || $this->parser->is(Tag::T_SWITCH)   // Done
        || $this->parser->is(Tag::T_TRY)      // Done
        || $this->parser->is(Tag::T_MATCH)    //
        || $this->parser->is(Tag::T_BREAK)    // Done
        || $this->parser->is(Tag::T_CONTINUE) // Done
        || $this->parser->is(Tag::T_GOTO)     // Done
        || $this->parser->is(Tag::T_GLOBAL)   // Done
        || $this->parser->is(Tag::T_STATIC)   //
        || $this->parser->is(Tag::T_RAISE)    // Done
        || $this->parser->is(Tag::T_PRINT)    // Done
        || $this->parser->is(Tag::T_OUT)      // Done
        || $this->parser->is('^')             // Done
        || $this->parser->is('[')             // Done
        || $this->parser->is(':-')            // Done
        || $this->startsExpr();               // Done
  }

  function startsExpr()
  {
    return $this->parser->is(Tag::T_INTEGER)
        || $this->parser->is(Tag::T_DOUBLE)
        || $this->parser->is(Tag::T_FN)
        || $this->parser->is(Tag::T_STATIC)
        || $this->parser->is(Tag::T_REQUIRE)
        || $this->parser->is(Tag::T_INCLUDE)
        || $this->parser->is(Tag::T_IDENT)
        || $this->parser->isOperator()
        || $this->parser->is('{')
        || $this->parser->is('(');
  }

  function startsClassDeclStmt()
  {
    return $this->parser->is(Tag::T_FINAL)
        || $this->parser->is(Tag::T_MODEL)
        || $this->parser->is(Tag::T_CLASS)
        || $this->parser->is(Tag::T_PIECE)
        || $this->parser->is(Tag::T_INTF);
  }

  function startsParameter()
  {
    return $this->parser->is('...')
        || $this->parser->is('*')
        || $this->parser->is(Tag::T_IDENT);
  }

  function startsCase()
  {
    return $this->parser->is(Tag::T_CASE)
        || $this->parser->is(Tag::T_ELSE);
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
    return $this->parser->is(Tag::T_FN)
        || $this->parser->is(Tag::T_CONST)
        || $this->parser->is(Tag::T_OPEN)
        || $this->parser->is(Tag::T_IDENT)
        || $this->isMethodModifier();
  }

  function isEoF()
  {
    return $this->parser->lookahead->getTag() === 0;
  }
}
