<?php

namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class ClassStmt implements Stmt
{
  public $category;
  public $name;
  public $extends;
  public $implements;
  public $body;

  public function __construct($category, $name, $extends, $implements, $body)
  {
    $this->category = $category;
    $this->name = $name;
    $this->extends = $extends;
    $this->implements = $implements;
    $this->body = $body;
  }

  public function format(Parser $parser)
  {
    throw new \Exception('TODO');
  }
}
