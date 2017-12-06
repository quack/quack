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

use \QuackCompiler\Ast\Decl\DataDecl;
use \QuackCompiler\Ast\Decl\FnShortDecl;
use \QuackCompiler\Ast\Decl\LetDecl;
use \QuackCompiler\Ast\Decl\TypeDecl;
use \QuackCompiler\Ast\Helpers\DataMember;
use \QuackCompiler\Ast\Helpers\Param;
use \QuackCompiler\Ast\Stmt\FnStmt;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;

class DeclParser
{
    use Attachable;

    public function __construct($reader)
    {
        $this->reader = $reader;
    }

    public function _letDecl()
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

       return new LetDecl($name, $type, $value, $mutable);
    }

    public function _param()
    {
        $name = $this->name_parser->_identifier();
        $type = null;
        if ($this->reader->consumeIf('::')) {
            $type = $this->type_parser->_type();
        }

        return new Param($name, $type);
    }

    public function _fnDecl()
    {
        $this->reader->match(Tag::T_FN);
        $name = $this->name_parser->_identifier();
        $params = [];
        $return_type = null;

        $this->reader->match('(');

        if (!$this->reader->consumeIf(')')) {
            do {
                $params[] = $this->_param();
            } while ($this->reader->consumeIf(','));
            $this->reader->match(')');
        }

        if ($this->reader->consumeIf(':')) {
            $return_type = $this->type_parser->_type();
        }

        if ($is_short = $this->reader->consumeIf(':-')) {
            $expr = $this->expr_parser->_expr();
            return new FnShortDecl($name, $params, $expr, $return_type);
        }

        // TODO: Support functions with imperative body
    }

    public function _typeDecl()
    {
        $this->reader->match(Tag::T_TYPE);
        $name = $this->name_parser->_typename();
        $this->reader->match(':-');
        $value = $this->type_parser->_type();

        return new TypeDecl($name, $value);
    }

    public function _dataMember()
    {
        $name = $this->name_parser->_typename();
        $values = [];
        if ($this->reader->consumeIf('(')) {
            do {
                $values[] = $this->type_parser->_type();
            } while ($this->reader->consumeIf(','));

            $this->reader->match(')');
        }

        return new DataMember($name, $values);
    }

    public function _dataDecl()
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
                $values[] = $this->_dataMember();
            } while ($this->reader->consumeIf(Tag::T_OR));
        }

        return new DataDecl($name, $parameters, $values);
    }
}
