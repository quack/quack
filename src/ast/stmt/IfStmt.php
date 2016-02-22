<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Ast\Stmt\BlockStmt;
use \QuackCompiler\Parser\Parser;

class IfStmt implements Stmt
{
  public $condition;
  public $body;
  public $elif;
  public $else;

  public function __construct($condition, $body, $elif, $else)
  {
    $this->condition = $condition;
    $this->body = $body;
    $this->elif = $elif;
    $this->else = $else;
  }

  public function format(Parser $parser)
  {
    $is_simple = !($this->body instanceof BlockStmt);
    $string_builder = ['if '];
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
