<?php

namespace UranoCompiler\Lexer;

class Token
{
  private $tag;
  private $pointer;
  private $symbol_table;

  public function __construct($tag, $pointer = NULL)
  {
    $this->tag = $tag;
    $this->pointer = $pointer;
  }

  public function getTag()
  {
    return $this->tag;
  }

  public function __toString()
  {
    if (isset($this->pointer)) {
      $tag_name = Tag::getName($this->tag);
      if (isset($this->symbol_table)) {
        return "[" . $tag_name . ", " . $this->symbol_table->get($this->pointer) . "]";
      } else {
        return "[" . $tag_name . ", " . $this->pointer . "]";
      }
    } else {
      return "[" . $this->tag . "]";
    }
  }

  public function showSymbolTable(SymbolTable &$symbol_table)
  {
    $this->symbol_table = $symbol_table;
  }
}
