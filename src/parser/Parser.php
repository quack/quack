<?php

namespace UranoCompiler\Parser;

use \Exception;
use \UranoCompiler\Lexer\Tokenizer;
use \UranoCompiler\Parser\SyntaxError;

abstract class Parser
{
  public $input;
  public $lookahead;

  public function __construct(Tokenizer $input)
  {
    $this->input = $input;
    $this->consume();
  }

  public function match($tag)
  {
    if ($this->lookahead->getTag() === $tag) {
      return $this->consume();
    }

    throw (new SyntaxError)->expected($tag)->found($this->lookahead)->on([
      "line"   => $this->input->line,
      "column" => $this->input->column
    ]);
  }

  public function opt($tag)
  {
    if ($this->lookahead->getTag() === $tag) {
      $pointer = $this->consume();
      return $pointer === NULL ? true : $pointer;
    }
    return false;
  }

  protected function is($tag)
  {
    return $this->lookahead->getTag() === $tag;
  }

  public function consume()
  {
    $pointer = $this->lookahead === NULL ?: $this->lookahead->getPointer();
    $this->lookahead = $this->input->nextToken();
    return $pointer;
  }

  protected function attachSymbolTable(&$token)
  {
    $token->showSymbolTable($this->input->getSymbolTable());
  }
}
