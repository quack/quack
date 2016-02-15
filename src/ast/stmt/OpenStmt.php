<?php

namespace UranoCompiler\Ast\Stmt;

use \UranoCompiler\Parser\Parser;

class OpenStmt implements Stmt
{
  public $module;
  public $alias;

  public function __construct($module, $alias = NULL)
  {
    $this->module = $module;
    $this->alias = $alias;
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
