<?php
/**
 * Quack Compiler and toolkit
 * Copyright (C) 2015-2017 Quack and CONTRIBUTORS
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

use \QuackCompiler\Ast\Helpers\Body;
use \QuackCompiler\Ast\Helpers\Program;
use \QuackCompiler\Ast\Stmt\ExprStmt;
use \QuackCompiler\Ast\Stmt\WhileStmt;
use \QuackCompiler\Lexer\Tag;

class StmtParser
{
    use Attachable;

    public function __construct($reader)
    {
        $this->reader = $reader;
    }

    public function startsStmt()
    {
        static $stmt_list = [
            Tag::T_LET, Tag::T_WHILE, Tag::T_DO,
            Tag::T_FN, Tag::T_TYPE, Tag::T_DATA
        ];

        $peek = $this->reader->lookahead->getTag();
        return in_array($peek, $stmt_list, true);
    }

    public function _program()
    {
        $body = [];
        while (!$this->reader->isEOF()) {
            $body[] = $this->_stmt();
        }
        return new Program($body);
    }

    public function _stmtList()
    {
        $stmt_list = [];
        while ($this->startsStmt()) {
            $stmt_list[] = $this->_stmt();
        }

        return new Body($stmt_list);
    }

    public function _stmt()
    {
        $decl_list = [
            Tag::T_DATA => '_dataDecl',
            Tag::T_FN   => '_fnDecl',
            Tag::T_LET  => '_letDecl',
            Tag::T_TYPE => '_typeDecl'
        ];

        $stmt_list = [
            Tag::T_WHILE    => '_whileStmt',
            Tag::T_DO       => '_exprStmt'
        ];

        $tag = $this->reader->lookahead->getTag();

        if (isset($decl_list[$tag])) {
            return $this->decl_parser->{$decl_list[$tag]}();
        }

        if (isset($stmt_list[$tag])) {
            return $this->{$stmt_list[$tag]}();
        }

        $params = [
            'expected' => 'statement or declaration',
            'found'    => $this->reader->lookahead,
            'parser'   => $this->reader
        ];

        if ($this->reader->isEOF()) {
            throw new EOFError($params);
        };

        throw new SyntaxError($params);
    }

    public function _exprStmt()
    {
        $this->reader->match(Tag::T_DO);
        $expr = $this->expr_parser->_expr();
        return new ExprStmt($expr);
    }

    public function _whileStmt()
    {
        $this->reader->match(Tag::T_WHILE);
        $condition = $this->expr_parser->_expr();
        $body = $this->_stmtList();
        $this->reader->match(Tag::T_END);

        return new WhileStmt($condition, $body);
    }
}
