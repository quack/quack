<?php

namespace UranoCompiler\Parser;

use \Exception;
use \UranoCompiler\Lexer\Tag;
use \UranoCompiler\Lexer\Tokenizer;

use \UranoCompiler\Ast\FunctionDecl;
use \UranoCompiler\Ast\ModuleStmt;
use \UranoCompiler\Ast\PrintStmt;
use \UranoCompiler\Ast\Expr;

class TokenReader extends Parser
{
  public $ast = [];

  public function __construct(Tokenizer $input)
  {
    parent::__construct($input);
  }

  /* Handlers */
  public function ast()
  {
    var_dump($this->ast);
  }

  public function parse()
  {
    foreach ($this->_topStmtList() as $stmt) {
      $this->ast[] = $stmt;
    }
  }

  /* Checkers */
  private function startsStmt()
  {
    return $this->is(Tag::T_MODULE);
  }

  private function isEOF()
  {
    return $this->lookahead->getTag() === 0;
  }

  /* Productions */
  private function _topStmtList()
  {
    while ($this->startsStmt()) {
      yield $this->_topStmt();
    }

    if (!$this->isEOF()) {
      throw (new SyntaxError)
        -> expected ('statement')
        -> found    ($this->lookahead)
        -> on       ($this->position())
        -> source   ($this->input);
    }
  }

  private function _topStmt()
  {
    if ($this->is(Tag::T_MODULE)) return $this->_module();
  }

  private function _module()
  {
    $this->match(Tag::T_MODULE);
    $module_name = $this->match(Tag::T_IDENT);
    return new ModuleStmt($this->resolveScope($module_name));
  }
}
