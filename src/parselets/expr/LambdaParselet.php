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
namespace QuackCompiler\Parselets\Expr;

use \QuackCompiler\Parser\Grammar;
use \QuackCompiler\Ast\Expr\Expr;
use \QuackCompiler\Ast\Expr\LambdaExpr;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;

class LambdaParselet implements IPrefixParselet
{
    const TYPE_EXPRESSION = 0x1;
    const TYPE_STATEMENT  = 0x2;

    public function parse(Grammar $grammar, Token $token)
    {
        $parameters = [];
        $kind = null;
        $body = null;
        $has_brackets = false;

        // When identifier, we have an unary function
        if ($grammar->parser->is(Tag::T_IDENT)) {
            $name = $grammar->identifier();
            $parameters[] = (object)[
                'name'         => $name,
                'is_reference' => false
            ];
        } else {
            $has_brackets = true;
            $grammar->parser->match('[');
            if (!$grammar->parser->consumeIf(']')) {
                $parameters[] = $grammar->_parameter();

                while ($grammar->parser->consumeIf(',')) {
                    $parameters[] = $grammar->_parameter();
                }

                $grammar->parser->match(']');
            }
        }

        $grammar->parser->match('->');

        if ($grammar->parser->is(Tag::T_BEGIN)) {
            $kind = static::TYPE_STATEMENT;
            $grammar->parser->consume();
            $body = iterator_to_array($grammar->_innerStmtList());
            $grammar->parser->match(Tag::T_END);
        } else {
            $kind = static::TYPE_EXPRESSION;
            $body = $grammar->_expr();
        }

        return new LambdaExpr($parameters, $kind, $body, $has_brackets);
    }
}
