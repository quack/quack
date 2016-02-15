<?php

namespace UranoCompiler\Ast\Expr;

use \UranoCompiler\Lexer\Tag;
use \UranoCompiler\Lexer\Token;
use \UranoCompiler\Parser\Parser;

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
}
