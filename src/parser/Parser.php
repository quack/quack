<?php

namespace UranoCompiler\Parser;

use \Exception;
use \UranoCompiler\Lexer\Tokenizer;

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

    throw new Exception("Expecting token {$tag}. Got {$this->lookahead}");
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
