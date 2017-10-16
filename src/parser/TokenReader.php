<?php
/**
 * Quack Compiler and toolkit
 * Copyright (C) 2016 Marcelo Camargo <marcelocamargo@linuxmail.org> and
 * CONTRIBUTORS.
 *
 * This file is part of Quack.
 *
 * Quack is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Quack is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Quack.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace QuackCompiler\Parser;

use \Exception;

use \QuackCompiler\Lexer\Tokenizer;

class TokenReader extends Parser
{
    private $main;
    public $ast = [];
    public $grammar;

    public function __construct(Tokenizer $input)
    {
        parent::__construct($input);
        $name_parser = new NameParser($this);
        $type_parser = new TypeParser($this);
        $expr_parser = new ExprParser($this);
        $decl_parser = new DeclParser($this);
        $stmt_parser = new StmtParser($this);

        $type_parser->attachParsers([
            'name_parser' => $name_parser
        ]);
        $expr_parser->attachParsers([
            'name_parser' => $name_parser,
            'stmt_parser' => $stmt_parser,
            'type_parser' => $type_parser
        ]);
        $decl_parser->attachParsers([
            'name_parser' => $name_parser,
            'expr_parser' => $expr_parser,
            'stmt_parser' => $stmt_parser,
            'type_parser' => $type_parser
        ]);
        $stmt_parser->attachParsers([
            'name_parser' => $name_parser,
            'type_parser' => $type_parser,
            'expr_parser' => $expr_parser,
            'decl_parser' => $decl_parser
        ]);

        $this->main = $stmt_parser;
    }

    /* Handlers */
    public function dumpAst()
    {
        var_dump($this->ast);
    }

    public function format()
    {
        echo $this->beautify();
    }

    public function beautify()
    {
        return $this->ast->format($this);
    }

    public function parse()
    {
        $this->ast = $this->main->_program();
    }

    public function evalParselet($grammar, $parselet)
    {
        $token = $this->consumeAndFetch();
        return (new $parselet)->parse($grammar, $token);
    }
}
