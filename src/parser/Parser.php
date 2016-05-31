<?php

namespace QuackCompiler\Parser;

use \Exception;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Lexer\Tokenizer;
use \QuackCompiler\Parselets\IPrefixParselet;
use \QuackCompiler\Parselets\IInfixParselet;
use \QuackCompiler\Parselets\BinaryOperatorParselet;
use \QuackCompiler\Parselets\NumberParselet;
use \QuackCompiler\Parselets\NameParselet;
use \QuackCompiler\Parselets\PostfixOperatorParselet;
use \QuackCompiler\Parselets\PrefixOperatorParselet;
use \QuackCompiler\Parselets\TernaryParselet;
use \QuackCompiler\Parselets\GroupParselet;
use \QuackCompiler\Parselets\FunctionParselet;
use \QuackCompiler\Parselets\IncludeParselet;
use \QuackCompiler\Parselets\ArrayParselet;
use \QuackCompiler\Parselets\NewParselet;
use \QuackCompiler\Parselets\MemberAccessParselet;

abstract class Parser
{
  public $input;
  public $lookahead;
  public $scope_level = 0;

  public $prefix_parselets = [];
  public $infix_parselets = [];

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
    $this->register(Tag::T_IDENT, new NameParselet);
    $this->register(Tag::T_THEN, new TernaryParselet);
    $this->register('(', new GroupParselet);
    $this->register('{', new ArrayParselet);
    $this->register(Tag::T_FN, new FunctionParselet);
    $this->register(Tag::T_STATIC, new FunctionParselet(true));
    $this->register(Tag::T_REQUIRE, new IncludeParselet);
    $this->register(Tag::T_INCLUDE, new IncludeParselet);
    $this->register('#', new NewParselet);
    $this->register(':', new MemberAccessParselet);
    $this->register('?:', new MemberAccessParselet);

    $this->prefix('+', Precedence::PREFIX);
    $this->prefix('-', Precedence::PREFIX);
    $this->prefix('^^', Precedence::PREFIX);
    $this->prefix('*', Precedence::PREFIX);
    $this->prefix('@', Precedence::PREFIX);
    $this->prefix('~', Precedence::PREFIX);
    $this->prefix(Tag::T_NOT, Precedence::PREFIX);

    $this->postfix('!', Precedence::POSTFIX);

    $this->infixLeft('+', Precedence::ADDITIVE);
    $this->infixLeft('-', Precedence::ADDITIVE);
    $this->infixLeft('++', Precedence::ADDITIVE);
    $this->infixLeft('*', Precedence::MULTIPLICATIVE);
    $this->infixLeft('/', Precedence::MULTIPLICATIVE);
    $this->infixLeft(Tag::T_MOD, Precedence::MULTIPLICATIVE);
    $this->infixLeft(Tag::T_AND, Precedence::LOGICAL_AND);
    $this->infixLeft(Tag::T_OR, Precedence::LOGICAL_OR);
    $this->infixLeft(Tag::T_XOR, Precedence::LOGICAL_XOR);
    $this->infixLeft('|', Precedence::BITWISE_OR);
    $this->infixLeft('&', Precedence::BITWISE_AND_OR_REF);
    $this->infixLeft('^', Precedence::BITWISE_XOR);
    $this->infixLeft('<<', Precedence::BITWISE_SHIFT);
    $this->infixLeft('>>', Precedence::BITWISE_SHIFT);
    $this->infixLeft('=', Precedence::VALUE_COMPARATOR);
    $this->infixLeft('<>', Precedence::VALUE_COMPARATOR);
    $this->infixLeft('<=', Precedence::SIZE_COMPARATOR);
    $this->infixLeft('<', Precedence::SIZE_COMPARATOR);
    $this->infixLeft('>=', Precedence::SIZE_COMPARATOR);
    $this->infixLeft('>', Precedence::SIZE_COMPARATOR);
    $this->infixLeft('|>', Precedence::PIPELINE);
    $this->infixLeft(Tag::T_INSTANCEOF, Precedence::O_INSTANCEOF);
    $this->infixLeft('??', Precedence::COALESCENCE);
    $this->infixLeft('?:', Precedence::TERNARY);

    $this->infixRight('**', Precedence::EXPONENT);
    $this->infixRight(':-', Precedence::ASSIGNMENT);
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

  public function is($tag)
  {
    return $this->lookahead->getTag() === $tag;
  }

  public function isOperator()
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

  public function position()
  {
    return ["line" => &$this->input->line, "column" => &$this->input->column];
  }

  public function infixParseletForToken(Token $token)
  {
    $key = $token->getTag();
    return array_key_exists($key, $this->infix_parselets)
      ? $this->infix_parselets[$key]
      : NULL;
  }

  public function prefixParseletForToken(Token $token)
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
