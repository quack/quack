<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Ast\Stmt\BlockStmt;
use \QuackCompiler\Parser\Parser;

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

  public function python(Parser $parser)
  {
    $is_simple = !($this->body instanceof BlockStmt);
    $string_builder = ['while '];
    $string_builder[] = $this->condition->python($parser);
    $string_builder[] = ":\n";

    if ($is_simple) {
      $string_builder[] = "\n";
      $parser->openScope();
      $string_builder[] = $parser->indent();
      $parser->closeScope();
    }

    $string_builder[] = $this->body->python($parser);
    return implode($string_builder);
  }
}
