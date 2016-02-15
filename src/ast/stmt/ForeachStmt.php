<?php

namespace UranoCompiler\Ast\Stmt;

use \UranoCompiler\Parser\Parser;

class ForeachStmt implements Stmt
{
  public $by_reference;
  public $alias;
  public $generator;
  public $body;

  public function __construct($by_reference, $alias, $generator, $body)
  {
    $this->by_reference = $by_reference;
    $this->alias = $alias;
    $this->generator = $generator;
    $this->body = $body;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
