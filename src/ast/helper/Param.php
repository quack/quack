<?php

namespace QuackCompiler\Ast\Helper;

use \QuackCompiler\Parser\Parser;

class Param
{
  public $name;
  public $by_reference;
  public $ellipsis;

  public function __construct($name, $by_reference, $ellipsis)
  {
    $this->name = $name;
    $this->by_reference = $by_reference;
    $this->ellipsis = $ellipsis;
  }

  public function format(Parser $parser)
  {
    $string_builder = [];
    if ($this->ellipsis) {
      $string_builder[] = '... ';
    }

    if ($this->by_reference) {
      $string_builder[] = '*';
    }

    $string_builder[] = $this->name;

    return implode($string_builder);
  }
}
