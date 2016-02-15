<?php

namespace UranoCompiler\Ast\Stmt;

use \UranoCompiler\Parser\Parser;

class DefStmt implements Stmt
{
  public $name;
  public $by_reference;
  public $body;
  public $parameters;

  public function __construct($name, $by_reference, $body, $parameters)
  {
    $this->name = $name;
    $this->by_reference = $by_reference;
    $this->body = $body;
    $this->parameters = $parameters;
  }

  public function format(Parser $parser)
  {
    $string_builder = ['def '];
    if ($this->by_reference) {
      $string_builder[] = '*';
    }
    $string_builder[] = $this->name;
    if (sizeof($this->parameters) > 0) {
      $string_builder[] = ' [';

      for ($i = 0, $l = sizeof($this->parameters); $i < $l; $i++) {
        $string_builder[] = $this->parameters[$i]->format($parser);

        if ($i !== $l - 1) {
          $string_builder[] = ', ';
        }
      }

      $string_builder[] = '] ';
    } else {
      $string_builder[] = '! ';
    }

    $string_builder[] = $this->body->format($parser);
    $string_builder[] = "";

    return implode($string_builder);
  }
}
