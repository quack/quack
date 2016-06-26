<?php
namespace QuackCompiler\Tests;

define('BASE_PATH', __DIR__ . '/../src');
require_once './src/toolkit/QuackToolkit.php';

use \QuackCompiler\Lexer\Tokenizer;
use \QuackCompiler\Parser\TokenReader;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testProvideBeautifier()
    {
        return function ($source) {
            $lexer = new Tokenizer($source);
            $parser = new TokenReader($lexer);
            $parser->parse();
            return $parser->beautify();
        };
    }

    /**
     * @depends testProvideBeautifier
     */
    public function testBreakStmt($beautifier)
    {
        $break = "break";
        $break_expr = "break 10";

        $this->assertEquals("break\n", $beautifier($break));
        $this->assertEquals("break 10\n", $beautifier($break_expr));
    }

    /**
     * @depends testProvideBeautifier
     */
    public function testContinueStmt($beautifier)
    {
        $continue = "continue";
        $continue_expr = "continue 10";

        $this->assertEquals("continue\n", $beautifier($continue));
        $this->assertEquals("continue 10\n", $beautifier($continue_expr));
    }
}
