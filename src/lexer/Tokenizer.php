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

      if (ctype_alpha($this->peek) || ($this->is('_') && ctype_alnum((string) $this->preview()))) {
        return $this->identifier();
      }

      if ($this->is(':') && (ctype_alpha((string) $preview = $this->preview()) || $preview === '_')) {
        return $this->atom();
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

    // Hexadecimal
    if ((int) $this->peek === 0 && strtolower($this->preview()) === 'x' && ctype_xdigit((string) $this->preview(2))) {
      $buffer[] = $this->readChar(); // 0
      $buffer[] = $this->readChar(); // x

      do {
        $buffer[] = $this->readChar();
      } while (ctype_xdigit((string) $this->peek));

      $hex_as_dec = (string) hexdec(implode($buffer));
      $this->column += sizeof($hex_as_dec);
      return new Token(Tag::T_INTEGER, $this->symbol_table->add($hex_as_dec));
    }

    // Is it decimal or octal?
    parse_int:
    do {
      $buffer[] = $this->readChar();
    } while (ctype_digit((string) $this->peek));

    if ($this->peek === '.' && ctype_digit((string) $this->preview()) && !$is_double) {
      $buffer[] = $this->readChar();
      $is_double = true;
      goto parse_int;
    }

    // Try to check for octal compatibility
    $is_octal = false;
    if (!$is_double && (int) $buffer[0] === 0 && sizeof($buffer) > 1) {
      $oct_buffer = [];
      $current_buffer_size = sizeof($buffer);

      $i = 0;
      while ($i < $current_buffer_size && in_array($buffer[$i], range(0, 7))) {
        $oct_buffer[] = $buffer[$i];
        $i++;
      }

      $oct_as_dec = (string) octdec(implode($oct_buffer));
      $this->column += $current_buffer_size;
      return new Token(Tag::T_INTEGER, $this->symbol_table->add($oct_as_dec));
    }

    // Decimal
    $size = sizeof($buffer);
    $buffer = implode($buffer);
    $buffer = $is_double ? (double) $buffer : (int) $buffer;

    $this->column += $size;
    return new Token($is_double ? Tag::T_DOUBLE : Tag::T_INTEGER, $this->symbol_table->add($buffer));
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

  private function atom()
  {
    $buffer = [];
    $this->consume();

    do {
      $buffer[] = $this->readChar();
    } while (ctype_alnum((string) $this->peek) || $this->peek === '_');

    $atom = implode($buffer);
    $this->column += sizeof($buffer) + 1;
    return new Token(Tag::T_ATOM, $this->symbol_table->add($atom));
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

  public function eagerlyEvaluate($show_symbol_table = false)
  {
    $this->rewind();
    $symbol_table = &$this->getSymbolTable();
    $token_stream = [];

    while ($this->peek != self::EOF) {
      $token_stream[] = $this->nextToken();
      if ($show_symbol_table) {
        $token_stream[sizeof($token_stream) - 1]->showSymbolTable($symbol_table);
      }
    }

    return $token_stream;
  }
}
