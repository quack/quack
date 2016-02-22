<?php

namespace QuackCompiler\Lexer;

class Word extends Token
{
  public $lexeme;

  public function __construct($tag, $word)
  {
    parent::__construct($tag);
    $this->lexeme = (string) $word;
  }

  public function __toString()
  {
    return "[" . $this->lexeme . "]";
  }
}
