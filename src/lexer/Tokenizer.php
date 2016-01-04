<?php

namespace UranoCompiler\Lexer;

class Tokenizer extends Lexer
{
  public $line = 0;
  public $column = 0;

  public function __construct($input)
  {
    parent::__construct($input);
  }

  public function nextToken()
  {
    while ($this->peek != self::EOF) {

      if (ctype_digit($this->peek)) {
        return $this->digit();
      }

      if (ctype_alpha($this->peek) || $this->is('_')) {
        return $this->identifier();
      }

      if (ctype_space($this->peek)) {
        $this->space();
        continue;
      }

      if ($this->matches('(*') && $this->previous() !== '&') {
        return $this->semanticComment();
      }

      if ($this->matches('//')) {
        $this->comment();
        continue;
      }

      if ($this->is('"') || $this->is("'")) {
        return $this->string($this->peek);
      }

      // Multichar symbol analysis
      return SymbolDecypher::{$this->peek}($this);
    }

    return new Token(self::EOF_TYPE);
  }

  private function digit()
  {
    $buffer = [];
    $is_double = false;

    parse_int:
    do {
      $buffer[] = $this->readChar();
    } while (ctype_digit($this->peek));

    if ($this->peek === '.' && ctype_digit($this->preview()) && !$is_double) {
      $buffer[] = $this->readChar();
      $is_double = true;
      goto parse_int;
    }

    $size = sizeof($buffer);
    $buffer = implode($buffer);
    $buffer = $is_double ? (double) $buffer : (int) $buffer;

    $this->column += $size;
    return new Token($is_double ? Tag::T_DOUBLE : Tag::T_INTEGER,
      $this->symbol_table->add($buffer));
  }

  private function identifier()
  {
    $buffer = [];

    do {
      $buffer[] = $this->readChar();
    } while (ctype_alnum((string) $this->peek) || $this->peek === '_');

    $string = implode($buffer);

    $word = $this->getWord($string);
    if ($word !== NULL) {
      return $word;
    }

    $this->column += sizeof($buffer);
    return new Token(Tag::T_IDENT, $this->symbol_table->add($string));
  }

  private function space()
  {
    $new_line = array_map('ord', ["\r", "\n", "\r\n", PHP_EOL]);

    do {
      if (in_array(ord($this->peek), $new_line)) {
        $this->line++;
        $this->column = 1;
      } else {
        $this->column++;
      }
      $this->consume();
    } while (ctype_space($this->peek));
  }

  private function string($delimiter)
  {
    $this->consume();

    $buffer = [];
    while (!$this->isEnd() && !($this->is($delimiter) && $this->previous() !== '\\')) {
      $buffer[] = $this->readChar();
    }

    $string = implode($buffer);

    if (!$this->isEnd()) {
      $this->consume();
    }

    return new Token(Tag::T_STRING, $this->symbol_table->add($string));
  }

  private function semanticComment()
  {
    $this->consume(2);

    $buffer = [];
    while (!$this->isEnd() && !($this->is('*') && $this->preview() === ')')) {
      $buffer[] = $this->readChar();
    }

    $comment = implode($buffer);

    if (!$this->isEnd()) {
      $this->consume(2);
    }

    return new Token(Tag::T_SEMANTIC_COMMENT, $this->symbol_table->add($comment));
  }

  private function comment()
  {
    $new_line = array_map('ord', ["\r", "\n", "\r\n", PHP_EOL]);
    do {
      $this->consume();
    } while (!in_array(ord($this->peek), $new_line));
  }

  private function readChar()
  {
    $char = $this->peek;
    $this->consume();
    return $char;
  }

  public function & getSymbolTable()
  {
    return $this->symbol_table;
  }
}
