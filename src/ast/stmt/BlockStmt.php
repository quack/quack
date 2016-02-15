<?php

namespace UranoCompiler\Ast\Stmt;

use \UranoCompiler\Parser\Parser;

class BlockStmt implements Stmt
{
  public $stmt_list;

  public function __construct($stmt_list)
  {
    $this->stmt_list = $stmt_list;
  }

  public function format(Parser $parser)
  {
    $string_builder = ['['];

    if (sizeof($this->stmt_list) > 0) {
      $string_builder[] = "\n";
    }

    foreach ($this->stmt_list as $stmt) {
      $string_builder[] = $parser->indent();
      $string_builder[] = $stmt->format($parser);
    }

    $string_builder[] = "]\n";
    return implode($string_builder);
  }
}
