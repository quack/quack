<?php

namespace UranoCompiler\Ast;

use \UranoCompiler\Lexer\Tag;
use \UranoCompiler\Lexer\Token;
use \UranoCompiler\Parser\Parser;

class PrefixExpression extends Expr
{
  private $operator;
  private $right;

  public function __construct(Token $operator, $right)
  {
    $this->operator = $operator->getTag();
    $this->right = $right;
  }

  public function format(Parser $parser)
  {
    return Tag::getPunctuator($this->operator) . ' ' . $this->right->format($parser);
  }
}
