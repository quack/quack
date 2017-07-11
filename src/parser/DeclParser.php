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

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;

use \QuackCompiler\Ast\Stmt\ClassStmt;
use \QuackCompiler\Ast\Stmt\EnumStmt;
use \QuackCompiler\Ast\Stmt\FnStmt;
use \QuackCompiler\Ast\Stmt\ImplStmt;
use \QuackCompiler\Ast\Stmt\ModuleStmt;
use \QuackCompiler\Ast\Stmt\OpenStmt;
use \QuackCompiler\Ast\Stmt\ShapeStmt;
use \QuackCompiler\Ast\Stmt\StmtList;

trait DeclParser
{
    public function _classDeclStmt()
    {
        $this->parser->match(Tag::T_CLASS);
        $name = $this->name_parser->_identifier();
        $body = iterator_to_array($this->_fnSignatureList());
        $this->parser->match(Tag::T_END);

        return new ClassStmt($name, $body);
    }

    public function _enumStmt()
    {
        $this->parser->match(Tag::T_ENUM);
        $entries = [];
        $name = $this->name_parser->_identifier();

        while ($this->parser->is(Tag::T_IDENT)) {
            $entries[] = $this->name_parser->_identifier();
        }

        $this->parser->match(Tag::T_END);

        return new EnumStmt($name, $entries);
    }

    public function _fnSignature()
    {
        $state = (object)[
            'is_recursive' => false,
            'is_reference' => false,
            'name'         => '<anonymous function>',
            'parameters'   => []
        ];

        $state->is_recursive = $this->parser->consumeIf(Tag::T_REC);
        $state->is_reference = $this->parser->consumeIf('*');
        $state->name = $this->name_parser->_identifier();

        $this->parser->match('(');

        if (!$this->parser->consumeIf(')')) {
            $state->parameters[] = $this->_parameter();

            while ($this->parser->consumeIf(',')) {
                $state->parameters[] = $this->_parameter();
            }

            $this->parser->match(')');
        }

        return $state;
    }

    public function _fnSignatureList()
    {
        while ($this->parser->is(Tag::T_IDENT)) {
            yield $this->_fnSignature();
        }
    }

    public function _fnStmt($is_method = false)
    {
        $is_pub = false;
        $is_short = false;
        $body = null;

        if (!$is_method) {
            $is_pub = $this->parser->consumeIf(Tag::T_PUB);
            $this->parser->match(Tag::T_FN);
        }

        $signature = $this->_fnSignature();

        // Is short method?
        if ($is_short = $this->parser->is(':-')) {
            $this->parser->consume(); // :-
            $body = $this->expr_parser->_expr();
        } else {
            $body = iterator_to_array($this->_innerStmtList());
            $this->parser->match(Tag::T_END);
        }

        return new FnStmt($signature, $body, $is_pub, $is_method, $is_short);
    }



    public function _implDeclStmt()
    {
        // Structs are for properties
        // Classes are for methods
        $type = Tag::T_SHAPE;
        $this->parser->match(Tag::T_IMPL);
        $class_or_shape = $this->name_parser->_qualifiedName();
        $class_for = null;
        // When it contains "for", it is being applied for a class
        if ($this->parser->is(Tag::T_FOR)) {
            $type = Tag::T_CLASS;
            $this->parser->consume();
            $class_for = $this->name_parser->_qualifiedName();
        }

        $body = new StmtList(iterator_to_array($this->_implStmtList()));
        $this->parser->match(Tag::T_END);

        return new ImplStmt($type, $class_or_shape, $class_for, $body);
    }

    public function _implStmtList()
    {
        while ($this->parser->is(Tag::T_IDENT)) {
            yield $this->_fnStmt(/* implicit */ true);
        }
    }

    public function _moduleStmt()
    {
        $this->parser->match(Tag::T_MODULE);
        return new ModuleStmt($this->name_parser->_qualifiedName());
    }

    public function _openStmt()
    {
        $this->parser->match(Tag::T_OPEN);
        $type = null;
        if ($this->parser->is(Tag::T_CONST) || $this->parser->is(Tag::T_FN)) {
            $type = $this->parser->consumeAndFetch();
        }

        $name = $this->parser->is('.') ? [$this->parser->consumeAndFetch()->getTag()] : [];
        $name[] = $this->name_parser->_qualifiedName();
        $alias = null;
        $subprops = null;

        if ($this->parser->is(Tag::T_AS)) {
            $this->parser->consume();
            $alias = $this->name_parser->_identifier();
        } elseif ($this->parser->is('{')) {
            $this->parser->consume();
            $subprops[] = $this->name_parser->_identifier();

            if ($this->parser->is(';')) {
                do {
                    $this->parser->match(';');
                    $subprops[] = $this->name_parser->_identifier();
                } while ($this->parser->is(';'));
            }

            $this->parser->match('}');
        }

        return new OpenStmt($name, $alias, $type, $subprops);
    }

    public function _shapeDeclStmt()
    {
        $this->parser->match(Tag::T_SHAPE);
        $name = $this->name_parser->_identifier();
        $members = [];

        while ($this->parser->is(Tag::T_IDENT)) {
            $members[] = $this->name_parser->_identifier();
        }

        $this->parser->match(Tag::T_END);

        return new ShapeStmt($name, $members);
    }
}
