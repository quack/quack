<?php

namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parser\Parser;

class OperatorExpr implements Expr
{
  public $left;
  public $operator;
  public $right;

  public function __construct(Expr $left, $operator, Expr $right)
  {
    $this->left = $left;
    $this->operator = $operator;
    $this->right = $right;
  }

  public function format(Parser $parser)
  {
    $string_builder = ['('];
    $string_builder[] = $this->left->format($parser);
    $string_builder[] = ' ';
    $string_builder[] = Tag::getPunctuator($this->operator);
    $string_builder[] = ' ';
    $string_builder[] = $this->right->format($parser);
    $string_builder[] = ')';
    return implode($string_builder);
  }

  public function python(Parser $parser)
  {
    $string_builder = ['('];
    $string_builder[] = $this->left->python($parser);
    $string_builder[] = ' ';
    $string_builder[] = Tag::getPunctuator($this->operator);
    $string_builder[] = ' ';
    $string_builder[] = $this->right->python($parser);
    $string_builder[] = ')';
    return implode($string_builder);
  }
}
