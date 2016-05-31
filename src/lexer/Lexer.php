<?php

namespace QuackCompiler\Lexer;

abstract class Lexer
{
  const EOF      = -1;
  const EOF_TYPE =  0;

  public $input;
  public $position = 0;
  public $peek;

  protected $words = [];

  public $symbol_table;

  public function __construct($input)
  {
    $this->size = strlen($input);

    if ($this->size === 0) {
      exit;
    }

    $this->symbol_table = new SymbolTable;
    $this->input = $input;
    $this->peek  = $input[0];

    // Reserve keywords
    $this->reserve(new Word(Tag::T_TRUE, "true"));
    $this->reserve(new Word(Tag::T_FALSE, "false"));
    $this->reserve(new Word(Tag::T_LET, "let"));
    $this->reserve(new Word(Tag::T_IF, "if"));
    $this->reserve(new Word(Tag::T_FOR, "for"));
    $this->reserve(new Word(Tag::T_WHILE, "while"));
    $this->reserve(new Word(Tag::T_DO, "do"));
    $this->reserve(new Word(Tag::T_STRUCT, "struct"));
    $this->reserve(new Word(Tag::T_INIT, "init"));
    $this->reserve(new Word(Tag::T_SELF, "self"));
    $this->reserve(new Word(Tag::T_MODULE, "module"));
    $this->reserve(new Word(Tag::T_CLASS, "class"));
    $this->reserve(new Word(Tag::T_OVERRIDE, "override"));
    $this->reserve(new Word(Tag::T_GOTO, "goto"));
    $this->reserve(new Word(Tag::T_FOREACH, "foreach"));
    $this->reserve(new Word(Tag::T_MATCH, "match"));
    $this->reserve(new Word(Tag::T_IN, "in"));
    $this->reserve(new Word(Tag::T_MODEL, "model"));
    $this->reserve(new Word(Tag::T_MOD, "mod"));
    $this->reserve(new Word(Tag::T_WHERE, "where"));
    $this->reserve(new Word(Tag::T_CONST, "const"));
    $this->reserve(new Word(Tag::T_MY, "my"));
    $this->reserve(new Word(Tag::T_NIL, "nil"));
    $this->reserve(new Word(Tag::T_STATIC, "static"));
    $this->reserve(new Word(Tag::T_PROTECTED, "protected"));
    $this->reserve(new Word(Tag::T_PROTOCOL, "protocol"));
    $this->reserve(new Word(Tag::T_FINAL, "final"));
    $this->reserve(new Word(Tag::T_TYPE_INT, "int"));
    $this->reserve(new Word(Tag::T_TYPE_DOUBLE, "double"));
    $this->reserve(new Word(Tag::T_TYPE_STRING, "string"));
    $this->reserve(new Word(Tag::T_TYPE_BOOL, "bool"));
    $this->reserve(new Word(Tag::T_TYPE_ARRAY, "array"));
    $this->reserve(new Word(Tag::T_TYPE_RESOURCE, "resource"));
    $this->reserve(new Word(Tag::T_TYPE_OBJECT, "object"));
    $this->reserve(new Word(Tag::T_OPEN, "open"));
    $this->reserve(new Word(Tag::T_GLOBAL, "global"));
    $this->reserve(new Word(Tag::T_AS, "as"));
    $this->reserve(new Word(Tag::T_TYPE, "type"));
    $this->reserve(new Word(Tag::T_ENUM, "enum"));
    $this->reserve(new Word(Tag::T_WITH, "with"));
    $this->reserve(new Word(Tag::T_CONTINUE, "continue"));
    $this->reserve(new Word(Tag::T_SWITCH, "switch"));
    $this->reserve(new Word(Tag::T_BREAK, "break"));
    $this->reserve(new Word(Tag::T_AND, "and"));
    $this->reserve(new Word(Tag::T_OR, "or"));
    $this->reserve(new Word(Tag::T_XOR, "xor"));
    $this->reserve(new Word(Tag::T_INSTANCEOF, "instanceof"));
    $this->reserve(new Word(Tag::T_TRY, "try"));
    $this->reserve(new Word(Tag::T_RESCUE, "rescue"));
    $this->reserve(new Word(Tag::T_FINALLY, "finally"));
    $this->reserve(new Word(Tag::T_RAISE, "raise"));
    $this->reserve(new Word(Tag::T_TYPE_CALLABLE, "callable"));
    $this->reserve(new Word(Tag::T_ELIF, "elif"));
    $this->reserve(new Word(Tag::T_ELSE, "else"));
    $this->reserve(new Word(Tag::T_CASE, "case"));
    $this->reserve(new Word(Tag::T_DECLARE, "declare"));
    $this->reserve(new Word(Tag::T_YIELD, "yield"));
    $this->reserve(new Word(Tag::T_SUPER, "super"));
    $this->reserve(new Word(Tag::T_PARTIAL, "partial"));
    $this->reserve(new Word(Tag::T_EXTENSION, "extension"));
    $this->reserve(new Word(Tag::T_IS, "is"));
    $this->reserve(new Word(Tag::T_OUT, "out"));
    $this->reserve(new Word(Tag::T_DERIVING, "deriving"));
    $this->reserve(new Word(Tag::T_LETF, "letf"));
    $this->reserve(new Word(Tag::T_PRINT, "print"));
    $this->reserve(new Word(Tag::T_NOT, "not"));
    $this->reserve(new Word(Tag::T_FN, "fn"));
    $this->reserve(new Word(Tag::T_REQUIRE, "require"));
    $this->reserve(new Word(Tag::T_INCLUDE, "include"));
    $this->reserve(new Word(Tag::T_ONCE, "once"));
    $this->reserve(new Word(Tag::T_PIECE, "piece"));
    $this->reserve(new Word(Tag::T_INTF, "intf"));
    $this->reserve(new Word(Tag::T_THEN, "then"));
  }

  private function reserve(Word $t)
  {
    $this->words[$t->lexeme] = $t;
  }

  protected function isEnd()
  {
    return $this->position >= $this->size;
  }

  public function rewind()
  {
    if ($this->size === 0) {
      exit;
    }

    $this->position = 0;
    $this->peek = $this->input[0];
  }

  public function consume($n = 1)
  {
    $this->position += $n;
    $this->peek = $this->isEnd()
      ? /* then      */ self::EOF
      : /* otherwise */ $this->input[$this->position];
  }

  public function preview($n = 1)
  {
    $next = $this->position + $n;

    return $next >= strlen($this->input)
      ? /* then      */ self::EOF
      : /* otherwise */ $this->input[$next];
  }

  public function previous()
  {
    $previous = $this->position - 1;
    return $previous < 0
      ? /* then      */ NULL
      : /* otherwise */ $this->input[$previous];
  }

  public function matches($string)
  {
    for ($i = 0; $i < strlen($string); $i++) {
      if ($this->preview($i) !== $string[$i]) {
        return false;
      }
    }
    return true;
  }

  protected function getWord($word)
  {
    return isset($this->words[$word])
      ? $this->words[$word]
      : NULL;
  }

  public function is($symbol)
  {
    return $this->peek === $symbol;
  }

  public function isNot($symbol)
  {
    return $this->peek !== $symbol;
  }

  public abstract function nextToken();
}
