<?php

namespace UranoCompiler\Ast\Stmt;

use \UranoCompiler\Ast\Stmt\BlockStmt;
use \UranoCompiler\Parser\Parser;

class WhileStmt implements Stmt
{
  public $condition;
  public $body;

  public function __construct($condition, $body)
  {
    $this->condition = $condition;
    $this->body = $body;
  }

  public function format(Parser $parser)
  {
    $is_simple = !($this->body instanceof BlockStmt);
    $string_builder = ['while '];
    $string_builder[] = $this->condition->format($parser);
    $string_builder[] = ' ';

    if ($is_simple) {
      $string_builder[] = "\n";
      $parser->openScope();
      $string_builder[] = $parser->indent();
      $parser->closeScope();
    }

    $string_builder[] = $this->body->format($parser);
    return implode($string_builder);
  }
}
