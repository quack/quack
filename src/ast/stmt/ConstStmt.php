<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class ConstStmt implements Stmt
{
  public $name;
  public $value;

  public function __construct($name, $value)
  {
    $this->name = $name;
    $this->value = $value;
  }

  public function format(Parser $parser)
  {
    $string_builder = ['const '];
    $string_builder[] = $this->name;
    $string_builder[] = ' :- ';
    $string_builder[] = $this->value->format($parser);
    return implode($string_builder);
  }
}
