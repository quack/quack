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

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Ast\Body;
use \QuackCompiler\Ast\Stmt\BlockStmt;
use \QuackCompiler\Ast\Stmt\BreakStmt;
use \QuackCompiler\Ast\Stmt\ContinueStmt;
use \QuackCompiler\Ast\Stmt\ElifStmt;
use \QuackCompiler\Ast\Stmt\ExprStmt;
use \QuackCompiler\Ast\Stmt\ForeachStmt;
use \QuackCompiler\Ast\Stmt\IfStmt;
use \QuackCompiler\Ast\Stmt\LabelStmt;
use \QuackCompiler\Ast\Stmt\LetStmt;
use \QuackCompiler\Ast\Stmt\ProgramStmt;
use \QuackCompiler\Ast\Stmt\ReturnStmt;
use \QuackCompiler\Ast\Stmt\TypeStmt;
use \QuackCompiler\Ast\Stmt\WhileStmt;

class StmtParser
{
    use Attachable;

    public $reader;

    public function __construct($reader)
    {
        $this->reader = $reader;
    }

    public function startsStmt()
    {
        static $stmt_list = [
            Tag::T_IF, Tag::T_LET, Tag::T_WHILE, Tag::T_DO, Tag::T_FOREACH,
            Tag::T_BREAK, Tag::T_CONTINUE, Tag::T_BEGIN, Tag::T_FN, '^', '[',
            Tag::T_TYPE, Tag::T_DATA
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
        return new ProgramStmt($body);
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
        $stmt_list = [
            Tag::T_IF       => '_ifStmt',
            Tag::T_LET      => '_letStmt',
            Tag::T_WHILE    => '_whileStmt',
            Tag::T_DO       => '_exprStmt',
            Tag::T_FOREACH  => '_foreachStmt',
            Tag::T_BREAK    => '_breakStmt',
            Tag::T_CONTINUE => '_continueStmt',
            Tag::T_BEGIN    => '_blockStmt',
            '^'             => '_returnStmt',
            '['             => '_labelStmt'
        ];

        if ($this->reader->is(Tag::T_FN)) {
            return $this->decl_parser->_fnStmt();
        }

        if ($this->reader->is(Tag::T_TYPE)) {
            return $this->decl_parser->_typeStmt();
        }

        if ($this->reader->is(Tag::T_DATA)) {
            return $this->decl_parser->_dataStmt();
        }

        if (isset($stmt_list[$this->reader->lookahead->getTag()])) {
            $callee = $stmt_list[$this->reader->lookahead->getTag()];
            return $this->{$callee}();
        }

        $params = [
            'expected' => 'statement',
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

    public function _blockStmt()
    {
        $this->reader->match(Tag::T_BEGIN);
        $body = $this->_stmtList();
        $this->reader->match(Tag::T_END);

        return new BlockStmt($body);
    }

    public function _ifStmt()
    {
        $this->reader->match(Tag::T_IF);
        $condition = $this->expr_parser->_expr();
        $body = $this->_stmtList();
        $elif = $this->_elifList();
        $else = $this->_optElse();
        $this->reader->match(Tag::T_END);

        return new IfStmt($condition, $body, $elif, $else);
    }

    public function _letStmt()
    {
        $this->reader->match(Tag::T_LET);
        $mutable = $this->reader->consumeIf(Tag::T_MUT);
        $name = $this->name_parser->_identifier();
        $type = $this->reader->consumeIf('::')
            ? $this->type_parser->_type()
            : null;
        $value = $this->reader->consumeIf(':-')
            ? $this->expr_parser->_expr()
            : null;

       return new LetStmt($name, $type, $value, $mutable);
    }

    public function _whileStmt()
    {
        $this->reader->match(Tag::T_WHILE);
        $condition = $this->expr_parser->_expr();
        $body = $this->_stmtList();
        $this->reader->match(Tag::T_END);

        return new WhileStmt($condition, $body);
    }

    public function _foreachStmt()
    {
        $key = null;
        $this->reader->match(Tag::T_FOREACH);

        if ($this->reader->is(Tag::T_IDENT)) {
            $alias = $this->name_parser->_identifier();

            if ($this->reader->consumeIf(':')) {
                $key = $alias;
                $alias = $this->name_parser->_identifier();
            }
        } else {
            $alias = $this->name_parser->_identifier();
        }

        $this->reader->match(Tag::T_IN);
        $iterable = $this->expr_parser->_expr();
        $body = $this->_stmtList();
        $this->reader->match(Tag::T_END);

        return new ForeachStmt($key, $alias, $iterable, $body);
    }

    public function _breakStmt()
    {
        $this->reader->match(Tag::T_BREAK);
        $label = $this->_optLabel();
        return new BreakStmt($label);
    }

    public function _continueStmt()
    {
        $this->reader->match(Tag::T_CONTINUE);
        $label = $this->_optLabel();
        return new ContinueStmt($label);
    }

    public function _returnStmt()
    {
        $this->reader->match('^');
        $expression = $this->expr_parser->_optExpr();

        return new ReturnStmt($expression);
    }

    public function _labelStmt()
    {
        $this->reader->match('[');
        $label_name = $this->name_parser->_identifier();
        $this->reader->match(']');
        $stmt = $this->_stmt();

        return new LabelStmt($label_name, $stmt);
    }

    public function _elifList()
    {
        $elifs = [];
        while ($this->reader->consumeIf(Tag::T_ELIF)) {
            $condition = $this->expr_parser->_expr();
            $body = $this->_stmtList();
            $elifs[] = new ElifStmt($condition, $body);
        }

        return $elifs;
    }

    public function _optElse()
    {
        if ($this->reader->consumeIf(Tag::T_ELSE)) {
            return $this->_stmtList();
        }

        return null;
    }

    public function _parameter()
    {
        $name = $this->name_parser->_identifier();
        $type = null;
        if ($this->reader->consumeIf('::')) {
            $type = $this->type_parser->_type();
        }

        return (object) [
            'name' => $name,
            'type' => $type
        ];
    }

    public function _optLabel()
    {
        return $this->reader->is(Tag::T_IDENT)
            ? $this->name_parser->_identifier()
            : null;
    }
}
