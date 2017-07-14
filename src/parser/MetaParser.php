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

use \QuackCompiler\Ast\Stmt\OperatorStmt;
use \QuackCompiler\Lexer\Tag;

class MetaParser
{
    use Attachable;

    public $reader;

    public function __construct($reader)
    {
        $this->reader = $reader;
    }

    public function _nativeStmt()
    {
        $this->reader->match(Tag::T_NATIVE);
        $class = $this->decl_parser->_classStmt();
        $class->native = true;
        return $class;
    }

    public function _operatorStmt()
    {
        $precedence = null;
        $type = $this->reader->lookahead->getTag();
        $this->reader->consume();

        if ($type !== Tag::T_PREFIX) {
            $precedence = (int) $this->reader->resolveScope(
                $this->reader->match(Tag::T_INTEGER)
            );
        }

        $this->reader->match('&(');
        $operator = $this->reader->nextValidOperator();
        $this->reader->match(')');

        return new OperatorStmt($type, $operator, $precedence);
    }
}
