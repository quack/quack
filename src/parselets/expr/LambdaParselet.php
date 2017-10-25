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
namespace QuackCompiler\Parselets\Expr;

use \QuackCompiler\Parser\Grammar;
use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Ast\Expr\LambdaExpr;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parselets\PrefixParselet;

class LambdaParselet implements PrefixParselet
{
    const TYPE_EXPRESSION = 0x1;
    const TYPE_STATEMENT  = 0x2;

    public function parse($grammar, Token $token)
    {
        $parameters = [];
        $kind = null;
        $body = null;
        $has_brackets = false;

        // When identifier, we have an unary function
        if ($grammar->reader->is(Tag::T_IDENT)) {
            $name = $grammar->name_parser->_identifier();
            $parameters[] = (object) [
                'name' => $name,
                'type' => null
            ];
        } else {
            $has_brackets = true;
            $grammar->reader->match('[');
            if (!$grammar->reader->consumeIf(']')) {
                do {
                    $parameters[] = $grammar->stmt_parser->_parameter();
                } while ($grammar->reader->consumeIf(','));
                $grammar->reader->match(']');
            }
        }

        $grammar->reader->match(':');

        if ($grammar->reader->is(Tag::T_BEGIN)) {
            $kind = static::TYPE_STATEMENT;
            $grammar->reader->consume();
            $body = $grammar->stmt_parser->_stmtList();
            $grammar->reader->match(Tag::T_END);
        } else {
            $kind = static::TYPE_EXPRESSION;
            $body = $grammar->_expr();
        }

        return new LambdaExpr($parameters, $kind, $body, $has_brackets);
    }
}
