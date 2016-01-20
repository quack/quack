<?php

namespace UranoCompiler\Parser;

use \Exception;
use \UranoCompiler\Lexer\Token;

define('BEGIN_ORANGE', "\033[01;31m");
define('END_ORANGE', "\033[0m");

class SyntaxError extends Exception
{
  private $expected;
  private $found;
  private $localization;

  public function expected($tag)
  {
    $this->expected = $tag;
    return $this;
  }

  public function found(Token $token)
  {
    $this->found = $token;
    return $this;
  }

  public function on(array $localization)
  {
    $this->localization = $localization;
    return $this;
  }

  public function __toString()
  {
    return implode(PHP_EOL, [
      BEGIN_ORANGE,
      "*** You have a syntactic error in your code!",
      "    Expecting [{$this->expected}]",
      "    Found     {$this->found}",
      "    Line      {$this->localization['line']}",
      "    Column    {$this->localization['column']}",
      END_ORANGE
    ]);
  }
}
