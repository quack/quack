<?php

namespace QuackCompiler\Lexer;

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

      if ((ctype_alpha($this->peek) || $this->is('_')) || ($this->is('_') && ctype_alnum((string) $this->preview()))) {
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

  public function digit()
  {
    $buffer = [];
    $is_double = false;

    // Hexadecimal or binary
    if ((int) $this->peek === 0 && in_array(strtolower($this->preview()), ['x','b'], true) && ctype_xdigit((string) $this->preview(2))) {
      $this->readChar(); // 0
      $type = strtolower($this->readChar()); // x or b

      do {
        $buffer[] = $this->readChar();
      } while ($type === 'x'
        ? (ctype_xdigit((string) $this->peek))
        : (in_array($this->peek, ['0', '1'], true)));

      $value = $type === 'x'
        ? (string) hexdec(implode($buffer))
        : bindec(implode($buffer));

      $this->column += sizeof($value);
      return new Token(Tag::T_INTEGER, $this->symbol_table->add($value));
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
    if (!$is_double && (int) $buffer[0] === 0 && sizeof($buffer) > 1) {
      $oct_buffer = [];
      $current_buffer_size = sizeof($buffer);

      $index = 0;
      while ($index < $current_buffer_size && in_array($buffer[$index], range(0, 7))) {
        $oct_buffer[] = $buffer[$index];
        $index++;
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
    $this->column += sizeof($buffer);

    if ($word !== NULL) {
      return $word;
    }

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

  public function printTokens()
  {
    $this->rewind();
    $symbol_table = &$this->getSymbolTable();

    $token = $this->nextToken();
    $token->showSymbolTable($symbol_table);

    while ($token->getTag() !== static::EOF_TYPE) {
      echo $token;
      $token = $this->nextToken();
      $token->showSymbolTable($symbol_table);
    }
  }
}
