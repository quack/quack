<?php

namespace UranoCompiler\Parser;

use \Exception;
use \UranoCompiler\Lexer\Tag;
use \UranoCompiler\Lexer\Token;
use \UranoCompiler\Lexer\Tokenizer;
use \UranoCompiler\Parselets\IPrefixParselet;
use \UranoCompiler\Parselets\IInfixParselet;
use \UranoCompiler\Parselets\BinaryOperatorParselet;
use \UranoCompiler\Parselets\NumberParselet;
use \UranoCompiler\Parselets\PostfixOperatorParselet;
use \UranoCompiler\Parselets\PrefixOperatorParselet;
use \UranoCompiler\Parselets\TernaryParselet;
use \UranoCompiler\Parselets\GroupParselet;

abstract class Parser
{
  public $input;
  public $lookahead;
  public $scope_level = 0;

  protected $prefix_parselets = [];
  protected $infix_parselets = [];

  public function __construct(Tokenizer $input)
  {
    $this->registerParselets();
    $this->input = $input;
    $this->consume();
  }

  private function register($tag, $parselet)
  {
    if ($parselet instanceof IPrefixParselet) {
      $this->prefix_parselets[$tag] = $parselet;
    } else if ($parselet instanceof IInfixParselet) {
      $this->infix_parselets[$tag] = $parselet;
    }
  }

  private function postfix($tag, $precedence)
  {
    $this->register($tag, new PostfixOperatorParselet($precedence));
  }

  private function prefix($tag, $precedence)
  {
    $this->register($tag, new PrefixOperatorParselet($precedence));
  }

  private function infixLeft($tag, $precedence)
  {
    $this->register($tag, new BinaryOperatorParselet($precedence, false));
  }

  private function infixRight($tag, $precedence)
  {
    $this->register($tag, new BinaryOperatorParselet($precedence, true));
  }

  private function registerParselets()
  {
    $this->register(Tag::T_INTEGER, new NumberParselet);
    $this->register(Tag::T_DOUBLE, new NumberParselet);
    $this->register('?', new TernaryParselet);
    $this->register('(', new GroupParselet);

    $this->prefix('+', Precedence::PREFIX);
    $this->prefix('-', Precedence::PREFIX);
    $this->prefix('^^', Precedence::PREFIX);
    $this->prefix('*', Precedence::PREFIX);
    $this->prefix('#', Precedence::PREFIX);
    $this->prefix('@', Precedence::PREFIX);
    $this->prefix('~', Precedence::PREFIX);
    $this->prefix(Tag::T_NOT, Precedence::PREFIX);

    $this->postfix('!', Precedence::POSTFIX);

    $this->infixLeft('+', Precedence::ADDITIVE);
    $this->infixLeft('-', Precedence::ADDITIVE);
    $this->infixLeft('*', Precedence::MULTIPLICATIVE);
    $this->infixLeft('/', Precedence::MULTIPLICATIVE);
    $this->infixLeft(Tag::T_AND, Precedence::LOGICAL_AND);
    $this->infixLeft(Tag::T_OR, Precedence::LOGICAL_OR);

    $this->infixRight('**', Precedence::EXPONENT);
  }

  public function match($tag)
  {
    if ($this->lookahead->getTag() === $tag) {
      return $this->consume();
    }

    throw (new SyntaxError)
      -> expected ($tag)
      -> found    ($this->lookahead)
      -> on       ($this->position())
      -> source   ($this->input);
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

  protected function isOperator()
  {
    $op = $this->lookahead->getTag();
    $op_table = array_values(Tag::getOpTable());
    return in_array($op, $op_table, true);
  }

  public function consume()
  {
    $pointer = $this->lookahead === NULL ?: $this->lookahead->getPointer();
    $this->lookahead = $this->input->nextToken();
    return $pointer;
  }

  public function consumeAndFetch()
  {
    $clone = $this->lookahead;
    $this->lookahead = $this->input->nextToken();
    return $clone;
  }

  public function resolveScope($pointer)
  {
    return $this->input->getSymbolTable()->get($pointer);
  }

  protected function position()
  {
    return ["line" => &$this->input->line, "column" => &$this->input->column];
  }

  protected function infixParseletForToken(Token $token)
  {
    $key = $token->getTag();
    return array_key_exists($key, $this->infix_parselets)
      ? $this->infix_parselets[$key]
      : NULL;
  }

  protected function prefixParseletForToken(Token $token)
  {
    $key = $token->getTag();
    return array_key_exists($key, $this->prefix_parselets)
      ? $this->prefix_parselets[$key]
      : NULL;
  }

  public function openScope()
  {
    $this->scope_level++;
  }

  public function closeScope()
  {
    $this->scope_level--;
  }

  public function indent()
  {
    return str_repeat('  ', $this->scope_level);
  }

  public function dedent()
  {
    return str_repeat('  ', $this->scope_level > 0 ? $this->scope_level - 1 : 0);
  }
}
