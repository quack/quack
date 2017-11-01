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
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Ast\Stmt\FnStmt;
use \QuackCompiler\Ast\Stmt\FnSignatureStmt;
use \QuackCompiler\Ast\Stmt\TypeStmt;
use \QuackCompiler\Ast\Stmt\TypeConsStmt;
use \QuackCompiler\Ast\Stmt\DataStmt;

class DeclParser
{
    use Attachable;

    public $reader;

    public function __construct($reader)
    {
        $this->reader = $reader;
    }

    public function _fnSignature()
    {
        $name = null;
        $parameters = [];
        $type = null;
        $name = $this->name_parser->_identifier();

        $this->reader->match('(');

        if (!$this->reader->consumeIf(')')) {
            do {
                $parameters[] = $this->stmt_parser->_parameter();
            } while ($this->reader->consumeIf(','));
            $this->reader->match(')');
        }

        if ($this->reader->consumeIf(':')) {
            $type = $this->type_parser->_type();
        }

        return new FnSignatureStmt($name, $parameters, $type);
    }

    public function _fnStmt()
    {
        $is_short = false;
        $body = null;

        $this->reader->match(Tag::T_FN);
        $signature = $this->_fnSignature();

        // Is short method?
        if ($is_short = $this->reader->is(':-')) {
            $this->reader->consume(); // :-
            $body = $this->expr_parser->_expr();
        } else {
            $body = $this->stmt_parser->_stmtList();
            $this->reader->match(Tag::T_END);
        }

        return new FnStmt($signature, $body, $is_short);
    }

    public function _typeStmt()
    {
        $this->reader->match(Tag::T_TYPE);
        $name = $this->name_parser->_typename();
        $this->reader->match(':-');
        $value = $this->type_parser->_type();

        return new TypeStmt($name, $value);
    }

    public function _typeConsStmt()
    {
        $name = $this->name_parser->_typename();
        $values = [];
        if ($this->reader->consumeIf('(')) {
            do {
                $values[] = $this->type_parser->_type();
            } while ($this->reader->consumeIf(','));

            $this->reader->match(')');
        }

        return new TypeConsStmt($name, $values);
    }

    public function _dataStmt()
    {
        $this->reader->match(Tag::T_DATA);
        $name = $this->name_parser->_typename();
        $parameters = [];
        $values = [];

        if ($this->reader->consumeIf('(')) {
            do {
                $parameters[] = $this->name_parser->_identifier();
            } while ($this->reader->consumeIf(','));

            $this->reader->match(')');
        }

        if ($this->reader->consumeIf(':-')) {
            do {
                $values[] = $this->_typeConsStmt();
            } while ($this->reader->consumeIf(Tag::T_OR));
        }

        return new DataStmt($name, $parameters, $values);
    }
}
