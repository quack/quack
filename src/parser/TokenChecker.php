<?php
/**
 * Quack Compiler and toolkit
 * Copyright (C) 2016 Marcelo Camargo <marcelocamargo@linuxmail.org> and
 * CONTRIBUTORS.
 *
 * This file is part of Quack.
 *
 * Quack is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Quack is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Quack.  If not, see <http://www.gnu.org/licenses/>.
 */
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
        || $this->parser->is(Tag::T_WHILE)    // Done
        || $this->parser->is(Tag::T_DO)       // Done
        || $this->parser->is(Tag::T_FOR)      // Done
        || $this->parser->is(Tag::T_FOREACH)  // Done
        || $this->parser->is(Tag::T_SWITCH)   // Done
        || $this->parser->is(Tag::T_TRY)      // Done
        || $this->parser->is(Tag::T_BREAK)    // Done
        || $this->parser->is(Tag::T_CONTINUE) // Done
        || $this->parser->is(Tag::T_GOTO)     // Done
        || $this->parser->is(Tag::T_GLOBAL)   // Done
        || $this->parser->is(Tag::T_RAISE)    // Done
        || $this->parser->is(Tag::T_PRINT)    // Done
        || $this->parser->is(Tag::T_BEGIN)    // Done
        || $this->parser->is('^')             // Done
        || $this->parser->is(':-');           // Done
  }

  function startsExpr()
  {
    return $this->parser->is(Tag::T_INTEGER)
        || $this->parser->is(Tag::T_DOUBLE)
        || $this->parser->is(Tag::T_FN)
        || $this->parser->is(Tag::T_REQUIRE)
        || $this->parser->is(Tag::T_INCLUDE)
        || $this->parser->is(Tag::T_IDENT)
        || $this->parser->is(Tag::T_TRUE)
        || $this->parser->is(Tag::T_FALSE)
        || $this->parser->is(Tag::T_NIL)
        || $this->parser->is(Tag::T_WHEN)
        || $this->parser->is(Tag::T_STRING)
        || $this->parser->is(Tag::T_ATOM)
        || $this->parser->isOperator()
        || $this->parser->is('{')
        || $this->parser->is('(');
  }

  function startsClassDeclStmt()
  {
    return $this->parser->is(Tag::T_CLASS);
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

  function startsClassStmt()
  {
    return $this->parser->is(Tag::T_FN)
        || $this->parser->is(Tag::T_CONST)
        || $this->parser->is(Tag::T_OPEN)
        || $this->parser->is(Tag::T_IDENT);
  }

  function isEoF()
  {
    return $this->parser->lookahead->getTag() === 0;
  }
}
