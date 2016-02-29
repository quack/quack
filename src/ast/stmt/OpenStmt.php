<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class OpenStmt implements Stmt
{
  public $module;
  public $alias;
  public $type;

  public function __construct($module, $alias = NULL, $type = NULL)
  {
    $this->module = $module;
    $this->alias = $alias;
    $this->type = $type;
  }

  public function format(Parser $parser)
  {
    $string_builder = ['open '];
    $string_builder[] = implode('.', $this->module);

    if (!is_null($this->alias)) {
      $string_builder[] = ' as ';
      $string_builder[] = $this->alias;
    }

    $string_builder[] = PHP_EOL;

    return implode($string_builder);
  }
}
