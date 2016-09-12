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
        $name = $this->identifier();
        $body = new StmtList(iterator_to_array($this->_nonBodiedMethodList()));
        $this->parser->match(Tag::T_END);

        return new ClassStmt($name, $body);
    }

    public function _enumStmt()
    {
        $this->parser->match(Tag::T_ENUM);
        $entries = [];
        $name = $this->identifier();

        while ($this->parser->is(Tag::T_IDENT)) {
            $entries[] = $this->identifier();
        }

        $this->parser->match(Tag::T_END);

        return new EnumStmt($name, $entries);
    }

    public function _fnStmt($needs_empty_body = false)
    {
        $by_reference = false;
        $is_bang = false;
        $is_pub = false;
        $is_rec = false;
        $parameters = [];
        $body = null;

        if ($this->parser->is(Tag::T_PUB)) {
            $is_pub = true;
            $this->parser->consume();
        }

        $this->parser->match(Tag::T_FN);

        if ($this->parser->is('*')) {
            $this->parser->consume();
            $by_reference = true;
        }

        if ($this->parser->is(Tag::T_REC)) {
            $this->parser->consume();
            $is_rec = true;
        }

        $name = $this->identifier();

        if ($is_bang = $this->parser->is('!')) {
            $this->parser->consume();
        } else {
            $this->parser->match('(');

            if ($this->parser->is(')')) {
                $this->parser->consume();
            } else {
                $parameters[] = $this->_parameter();

                while ($this->parser->is(';')) {
                    $this->parser->consume();
                    $parameters[] = $this->_parameter();
                }

                $this->parser->match(')');
            }
        }

        if (!$needs_empty_body) {
            $body = iterator_to_array($this->_innerStmtList());
            $this->parser->match(Tag::T_END);
        }

        return new FnStmt($name, $by_reference, $body, $parameters, $is_bang, $is_pub, $is_rec);
    }



    public function _implStmt()
    {
        // Structs are for properties
        // Classes are for methods
        $type = Tag::T_STRUCT;
        $this->parser->match(Tag::T_IMPL);
        $class_or_shape = $this->qualifiedName();
        $class_for = null;
        // When it contains "for", it is being applied for a class
        if ($this->parser->is(Tag::T_FOR)) {
            $type = Tag::T_CLASS;
            $this->parser->consume();
            $class_for = $this->qualifiedName();
        }

        $body = new StmtList(iterator_to_array($this->_blueprintStmtList()));
        $this->parser->match(Tag::T_END);

        return new ImplStmt($type, $class_or_shape, $class_for, $body);
    }

    public function _moduleStmt()
    {
        $this->parser->match(Tag::T_MODULE);
        return new ModuleStmt($this->qualifiedName());
    }

    public function _openStmt()
    {
        $this->parser->match(Tag::T_OPEN);
        $type = null;
        if ($this->parser->is(Tag::T_CONST) || $this->parser->is(Tag::T_FN)) {
            $type = $this->parser->consumeAndFetch();
        }

        $name = $this->parser->is('.') ? [$this->parser->consumeAndFetch()->getTag()] : [];
        $name[] = $this->qualifiedName();
        $alias = null;
        $subprops = null;

        if ($this->parser->is(Tag::T_AS)) {
            $this->parser->consume();
            $alias = $this->identifier();
        } elseif ($this->parser->is('{')) {
            $this->parser->consume();
            $subprops[] = $this->identifier();

            if ($this->parser->is(';')) {
                do {
                    $this->parser->match(';');
                    $subprops[] = $this->identifier();
                } while ($this->parser->is(';'));
            }

            $this->parser->match('}');
        }

        return new OpenStmt($name, $alias, $type, $subprops);
    }

    public function _shapeDeclStmt()
    {
        $this->parser->match(Tag::T_SHAPE);
        $name = $this->identifier();
        $members = [];

        while ($this->parser->is(Tag::T_IDENT)) {
            $members[] = $this->identifier();
        }

        $this->parser->match(Tag::T_END);

        return new ShapeStmt($name, $members);
    }
}
