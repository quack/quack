<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class ModuleStmt implements Stmt
{
  public $qualified_name;

  public function __construct($qualified_name)
  {
    $this->qualified_name = $qualified_name;
  }

  public function format(Parser $parser)
  {
    $string_builder = ['module '];
    $string_builder[] = implode('.', $this->qualified_name);
    $string_builder[] = PHP_EOL;
    return implode($string_builder);
  }
}
