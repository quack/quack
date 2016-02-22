<?php

namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parser\Parser;

class PrefixExpr implements Expr
{
  private $operator;
  private $right;

  public function __construct(Token $operator, Expr $right)
  {
    $this->operator = $operator->getTag();
    $this->right = $right;
  }

  public function format(Parser $parser)
  {
    $string_builder = [];
    if ($this->operator === Tag::T_NOT) {
      $string_builder[] = 'not ';
    } else {
      $string_builder[] = $this->operator;
    }

    $string_builder[] = $this->right->format($parser);

    return implode($string_builder);
  }

  public function python(Parser $parser)
  {
    $string_builder = [];
    if ($this->operator === Tag::T_NOT) {
      $string_builder[] = '!';
    } else if ($this->operator === '*') {
      // pass
    } else {
      $string_builder[] = $this->operator;
    }

    $string_builder[] = $this->right->python($parser);
    return implode($string_builder);
  }
}
