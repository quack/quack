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
    if (sizeof($this->stmt_list) == 0) {
      return "[]\n";
    }

    $string_builder = ["[\n"];
    $parser->openScope();

    foreach ($this->stmt_list as $stmt) {
      $string_builder[] = $parser->indent() . $stmt->format($parser);
    }

    $string_builder[] = $parser->dedent();
    $parser->closeScope();
    $string_builder[] = "]\n";
    return implode($string_builder);
  }
}
