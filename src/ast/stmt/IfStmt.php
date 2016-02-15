<?php

namespace UranoCompiler\Ast\Stmt;

use \UranoCompiler\Parser\Parser;

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
    $string_builder = ['if '];
    $string_builder[] = $this->condition->format($parser);
    $string_builder[] = ' ';
    $string_builder[] = $this->blocklize($this->body);
    $string_builder[] = PHP_EOL;

    if (sizeof($this->elif) > 0 || !is_null($this->else)) {
      $string_builder[] = ' ';
    }

    foreach ($this->elif as $elif) {
      $string_builder[] = 'elif ';
      $string_builder[] = $elif['condition']->format($parser);
      $string_builder[] = ' ';
      $string_builder[] = $this->blocklize($elif['body']);
      $string_builder[] = ' ';
    }

    if (!is_null($this->else)) {
      $string_builder[] = 'else ';
      $string_builder[] = $this->blocklize($this->else);
    }

    $string_builder[] = PHP_EOL;

    return implode($string_builder);
  }

  private function blocklize($body, Parser $parser)
  {
    $string_builder = [];

    if ($body instanceof BlockStmt) {
      $string_builder[] = $body->format($parser);
    } else {
      $string_builder[] = '[';
      $string_builder[] = PHP_EOL;
      $string_builder[] = '  ';
      $string_builder[] = $body->format($parser);
      $string_builder[] = ']';
    }

    return implode($string_builder);
  }
}
