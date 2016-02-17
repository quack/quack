<?php

require_once './src/toolkit/TestCaseToolkit.php';

use \UranoCompiler\Lexer\Tokenizer;

define('SHOW_SYMBOL_TABLE', true);

class LexerTest extends PHPUnit_Framework_TestCase
{
  private function tokenize($source, $show_symbol_table = false)
  {
    return implode(array_map(function($token) use ($show_symbol_table) {
      return (string) $token;
    }, (new Tokenizer($source))->eagerlyEvaluate($show_symbol_table)));
  }

  public function testIdent()
  {
    $this->assertEquals("[T_IDENT, 0]", $this->tokenize('urano'));
    $this->assertEquals("[T_IDENT, urano]", $this->tokenize('urano', SHOW_SYMBOL_TABLE));
    $this->assertEquals("[T_IDENT, 0][T_IDENT, 1]", $this->tokenize('hello world'));
    $this->assertEquals("[T_IDENT, hello][T_IDENT, world]", $this->tokenize('hello world', SHOW_SYMBOL_TABLE));
  }

  public function testNumber()
  {
    $decimal_integer = "1083";
    $octal_integer = "0314";
    $octal_partial_integer = "0314891";
    $hexa_integer = "0xFFAB01";
    $hexa_partial_integer = "0xFFAB01ZD";
    $decimal_double = "124.1323";
    $decimal_non_octal_double = "0314.0";

    $this->assertEquals("[T_INTEGER, 1083]", $this->tokenize($decimal_integer, SHOW_SYMBOL_TABLE));
    $this->assertEquals("[T_INTEGER, 204]", $this->tokenize($octal_integer, SHOW_SYMBOL_TABLE));
    $this->assertEquals("[T_INTEGER, 204]", $this->tokenize($octal_partial_integer, SHOW_SYMBOL_TABLE));
    $this->assertEquals("[T_INTEGER, 16755457]", $this->tokenize($hexa_integer, SHOW_SYMBOL_TABLE));
    $this->assertEquals("[T_INTEGER, 16755457]", $this->tokenize($hexa_partial_integer, SHOW_SYMBOL_TABLE));
    $this->assertEquals("[T_DOUBLE, 124.1323]", $this->tokenize($decimal_double, SHOW_SYMBOL_TABLE));
    $this->assertEquals("[T_DOUBLE, 314]", $this->tokenize($decimal_non_octal_double, SHOW_SYMBOL_TABLE));
  }

  public function testSemanticComment()
  {
    $partial_function = "&(* 1)";
    $semantic_comment = "(* Some comment *)";

    $this->assertEquals("[&(][*][T_INTEGER, 1][)]", $this->tokenize($partial_function, SHOW_SYMBOL_TABLE));
    $this->assertEquals("[T_SEMANTIC_COMMENT,  Some comment ]", $this->tokenize($semantic_comment, SHOW_SYMBOL_TABLE));
    $this->assertEquals("[T_SEMANTIC_COMMENT, 0]", $this->tokenize($semantic_comment));
  }

  public function testString()
  {
    $string = "'lorem ipsum dolor'";
    $string_with_quote = "'lorem ipsum \' dolor'";
    $string_with_double_quote = "'lorem ipsum \" dolor";
    $complex_string = '"complex \" string"';
    $simple_string = '"simple \' string"';

    $this->assertEquals('[T_STRING, lorem ipsum dolor]', $this->tokenize($string, SHOW_SYMBOL_TABLE));
    $this->assertEquals('[T_STRING, lorem ipsum \\\' dolor]', $this->tokenize($string_with_quote, SHOW_SYMBOL_TABLE));
    $this->assertEquals('[T_STRING, lorem ipsum " dolor]', $this->tokenize($string_with_double_quote, SHOW_SYMBOL_TABLE));
    $this->assertEquals('[T_STRING, complex \\" string]', $this->tokenize($complex_string, SHOW_SYMBOL_TABLE));
    $this->assertEquals('[T_STRING, simple \' string]', $this->tokenize($simple_string, SHOW_SYMBOL_TABLE));
  }

  public function testTag()
  {
    $atom = 'some :atom';
    $this->assertEquals("[T_IDENT, some][T_ATOM, atom]", $this->tokenize($atom, SHOW_SYMBOL_TABLE));
  }

  public function testParam()
  {
    $some_parameters = "&0, &1, &(2)";
    $this->assertEquals("[T_PARAM, 0][,][T_PARAM, 1][,][&(][T_INTEGER, 2][)]", $this->tokenize($some_parameters, SHOW_SYMBOL_TABLE));
  }
}
