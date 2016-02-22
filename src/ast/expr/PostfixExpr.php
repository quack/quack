<?php

namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parser\Parser;

class PostfixExpr implements Expr
{
  public $left;
  public $operator;

  public function __construct($left, $operator)
  {
    $this->left = $left;
    $this->operator = $operator;
  }

  public function format(Parser $parser)
  {
    $string_builder = ['('];
    $string_builder[] = $this->left->format($parser);
    $string_builder[] = Tag::getPunctuator($this->operator);
    $string_builder[] = ')';
    return implode($string_builder);
  }

  public function python(Parser $parser)
  {
    $string_builder = ['('];
    $string_builder[] = $this->left->python($parser);
    $string_builder[] = Tag::getPunctuator($this->operator);
    $string_builder[] = ')';
    return implode($string_builder);
  }
}
