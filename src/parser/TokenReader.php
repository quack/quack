<?php

namespace QuackCompiler\Parser;

use \Exception;

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Tokenizer;

use \QuackCompiler\Ast\Stmt\BlockStmt;
use \QuackCompiler\Ast\Stmt\BreakStmt;
use \QuackCompiler\Ast\Stmt\ContinueStmt;
use \QuackCompiler\Ast\Stmt\DefStmt;
use \QuackCompiler\Ast\Stmt\ExprStmt;
use \QuackCompiler\Ast\Stmt\ForeachStmt;
use \QuackCompiler\Ast\Stmt\GlobalStmt;
use \QuackCompiler\Ast\Stmt\GotoStmt;
use \QuackCompiler\Ast\Stmt\IfStmt;
use \QuackCompiler\Ast\Stmt\LabelStmt;
use \QuackCompiler\Ast\Stmt\PrintStmt;
use \QuackCompiler\Ast\Stmt\RaiseStmt;
use \QuackCompiler\Ast\Stmt\ReturnStmt;
use \QuackCompiler\Ast\Stmt\WhileStmt;

use \QuackCompiler\Ast\Helper\Param;

class TokenReader extends Parser
{
  public $ast = [];
  public $grammar;

  public function __construct(Tokenizer $input)
  {
    parent::__construct($input);
    $this->grammar = new Grammar($this);
  }

  /* Handlers */
  public function dumpAst()
  {
    foreach ($this->input->symbol_table->iterator() as $key => $value) {
      echo '[', BEGIN_GREEN, $key, END_GREEN, ': ', BEGIN_BOLD,  $value, END_BOLD, ']', PHP_EOL;
    }

    var_dump($this->ast);
  }

  public function format()
  {
    foreach ($this->ast as $stmt) {
      echo $stmt->format($this);
    }
  }

  public function beautify()
  {
    $source = [];
    foreach ($this->ast as $stmt) {
      $source[] = $stmt->format($this);
    }
    return implode($source);
  }

  public function python()
  {
    foreach ($this->ast as $stmt) {
      echo $stmt->python($this);
    }
  }

  public function parse()
  {
    $this->ast = $this->grammar->start();
  }
}
