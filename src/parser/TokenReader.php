<?php

namespace UranoCompiler\Parser;

use \Exception;
use \UranoCompiler\Lexer\Tag;
use \UranoCompiler\Lexer\Tokenizer;

use \UranoCompiler\Ast\Ast;
use \UranoCompiler\Ast\PrintStmt;
use \UranoCompiler\Ast\Expr;

class TokenReader extends Parser
{
  public $ast = [];

  public function __construct(Tokenizer $input)
  {
    parent::__construct($input);
  }

  public function ast($scope = 0, $tree = NULL)
  {
    $symbol_table = &$this->input->getSymbolTable();
    $level = str_repeat(' ', $scope * 2);

    $tree = $tree === NULL ? $this->ast[0] : $tree;
    if (isset($tree->value)) {
      echo $level . get_class($tree) . PHP_EOL;
      $this->ast($scope + 1, $tree->value);
    } else {
      echo $level . $symbol_table->get($tree) . PHP_EOL;
    }
  }

  public function parse()
  {
    throw (new SyntaxError())->expected('let')->found($this->lookahead)->on([
      "line"   => $this->input->line,
      "column" => $this->input->column
    ]);
    $this->ast[] = new Ast($this->_print());
  }

  public function _print()
  {
    $this->match(Tag::T_PRINT);
    return new PrintStmt(new Expr($this->match(Tag::T_INTEGER)));
  }
}
