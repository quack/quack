<?php
define('BASE_PATH', __DIR__ . '/../src');
require_once './src/toolkit/QuackToolkit.php';

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Lexer\Tokenizer;
use \QuackCompiler\Parser\TokenReader;

use \QuackCompiler\Ast\Expr\NumberExpr;
use \QuackCompiler\Ast\Expr\OperatorExpr;
use \QuackCompiler\Ast\Expr\PostfixExpr;
use \QuackCompiler\Ast\Expr\PrefixExpr;
use \QuackCompiler\Ast\Expr\TernaryExpr;

class AstTest extends PHPUnit_Framework_TestCase
{
  public function format($source, $expr)
  {
    $lexer = new Tokenizer($source);
    $parser = new TokenReader($lexer);
    $parser->parse();
    return $expr->format($parser);
  }

  public function testNumber()
  {
    $double = "10.99.";
    $integer = "3123.";
    $hexa = "0xABC.";
    $octal = "0765.";

    $this->assertEquals("10.99", $this->format($double, new NumberExpr(new Token(Tag::T_DOUBLE, 0))));
    $this->assertEquals("3123", $this->format($integer, new NumberExpr(new Token(Tag::T_INTEGER, 0))));
    $this->assertEquals("2748", $this->format($hexa, new NumberExpr(new Token(Tag::T_INTEGER, 0))));
    $this->assertEquals("501", $this->format($octal, new NumberExpr(new Token(Tag::T_INTEGER, 0))));
  }

  public function testOperator()
  {
    $prefix_precedence = "-1 * +~2.";


    $this->assertEquals("(-1 * +~2)", $this->format($prefix_precedence,
      new OperatorExpr(
        new PrefixExpr(
          new Token('-'), new NumberExpr(new Token(Tag::T_INTEGER, 0))
        ),
        '*',
        new PrefixExpr(
          new Token('+'),
          new PrefixExpr(
            new Token('~'),
            new NumberExpr(new Token(Tag::T_INTEGER, 1))
          )
        )
      ))
    );
  }

  public function testTernaryOperator()
  {
    $source = "10 and 2 then 1 else 2 and 3 then 4 else 5.";

    $this->assertEquals("((10 and 2) ? 1 : ((2 or 3) ? 4 : 5))",
      $this->format($source,
        new TernaryExpr(
          new OperatorExpr(
            new NumberExpr(
              new Token(Tag::T_INTEGER, 0)
            ),
            Tag::T_AND,
            new NumberExpr(
              new Token(Tag::T_AND, 1)
            )
          ), /* condition 1 */
          new NumberExpr(
            new Token(Tag::T_INTEGER, 2)
          ), /* then 1 */
          new TernaryExpr(
            new OperatorExpr(
              new NumberExpr(
                new Token(Tag::T_INTEGER, 3)
              ),
              Tag::T_OR,
              new NumberExpr(
                new Token(Tag::T_INTEGER, 4)
              )
            ), /* condition 2 */
            new NumberExpr(
              new Token(Tag::T_INTEGER, 5)
            ), /* then 2 */
            new NumberExpr(
              new Token(Tag::T_INTEGER, 6)
            ) /* else 2 */
          ) /* else 1 */
        )
      )
    );
  }
}
