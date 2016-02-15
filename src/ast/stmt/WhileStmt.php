<?php

namespace UranoCompiler\Ast\Stmt;

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
    $string_builder = ['while '];
    $string_builder[] = $this->condition->format($parser);
    $string_builder[] = ' ';

    if ($this->body instanceof BlockStmt) {
      $string_builder[] = $this->body->format($parser);
    } else {
      $string_builder[] = '[';
      $string_builder[] = PHP_EOL;
      $string_builder[] = '  ';
      $string_builder[] = $this->body->format($parser);
      $string_builder[] = ']';
    }

    $string_builder[] = PHP_EOL;

    return implode($string_builder);
  }
}
