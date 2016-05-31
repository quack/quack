<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class FnStmt implements Stmt
{
  public $name;
  public $by_reference;
  public $body;
  public $parameters;
  public $modifiers = [];

  public function __construct($name, $by_reference, $body, $parameters, $modifiers = [])
  {
    $this->name = $name;
    $this->by_reference = $by_reference;
    $this->body = $body;
    $this->parameters = $parameters;
    $this->modifiers = $modifiers;
  }

  public function format(Parser $parser)
  {
    $string_builder = ['fn '];
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

    return implode($string_builder);
  }
}
