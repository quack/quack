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
use \QuackCompiler\Ast\Stmt\BlockStmt;
use \QuackCompiler\Ast\Stmt\BreakStmt;
use \QuackCompiler\Ast\Stmt\CaseStmt;
use \QuackCompiler\Ast\Stmt\ConstStmt;
use \QuackCompiler\Ast\Stmt\ContinueStmt;
use \QuackCompiler\Ast\Stmt\ElifStmt;
use \QuackCompiler\Ast\Stmt\ExprStmt;
use \QuackCompiler\Ast\Stmt\ForeachStmt;
use \QuackCompiler\Ast\Stmt\ForStmt;
use \QuackCompiler\Ast\Stmt\IfStmt;
use \QuackCompiler\Ast\Stmt\LabelStmt;
use \QuackCompiler\Ast\Stmt\LetStmt;
use \QuackCompiler\Ast\Stmt\PostConditionalStmt;
use \QuackCompiler\Ast\Stmt\ProgramStmt;
use \QuackCompiler\Ast\Stmt\RaiseStmt;
use \QuackCompiler\Ast\Stmt\ReturnStmt;
use \QuackCompiler\Ast\Stmt\SwitchStmt;
use \QuackCompiler\Ast\Stmt\TryStmt;
use \QuackCompiler\Ast\Stmt\WhileStmt;
use \QuackCompiler\Ast\Stmt\StmtList;

class StmtParser
{
    use Attachable;

    public $reader;
    public $checker;

    public function __construct($reader)
    {
        $this->reader = $reader;
        $this->checker = new TokenChecker($reader);
    }

    public function _program()
    {
        return new ProgramStmt(iterator_to_array($this->_topStmtList()));
    }

    public function _topStmtList()
    {
        while (!$this->checker->isEoF()) {
            yield $this->_topStmt();
        }
    }

    public function _innerStmtList()
    {
        while ($this->checker->startsInnerStmt()) {
            yield $this->_innerStmt();
        }
    }

    public function _stmt()
    {
        $branch_table = [
            Tag::T_IF       => '_ifStmt',
            Tag::T_LET      => '_letStmt',
            Tag::T_CONST    => '_constStmt',
            Tag::T_WHILE    => '_whileStmt',
            Tag::T_DO       => '_exprStmt',
            Tag::T_FOR      => '_forStmt',
            Tag::T_FOREACH  => '_foreachStmt',
            Tag::T_SWITCH   => '_switchStmt',
            Tag::T_TRY      => '_tryStmt',
            Tag::T_BREAK    => '_breakStmt',
            Tag::T_CONTINUE => '_continueStmt',
            Tag::T_RAISE    => '_raiseStmt',
            Tag::T_BEGIN    => '_blockStmt',
            '^'             => '_returnStmt',
            '['             => '_labelStmt'
        ];

        foreach ($branch_table as $token => $action) {
            if ($this->reader->is($token)) {
                $first_class_stmt = $this->{$action}();

                // Optional postfix notation for statements
                if ($this->reader->is(Tag::T_WHEN) || $this->reader->is(Tag::T_UNLESS)) {
                    $tag = $this->reader->consumeAndFetch()->getTag();
                    $predicate = $this->expr_parser->_expr();

                    return new PostConditionalStmt($first_class_stmt, $predicate, $tag);
                }

                return $first_class_stmt;
            }
        }

        $params = [
            'expected' => 'statement',
            'found'    => $this->reader->lookahead,
            'parser'   => $this->reader
        ];

        if (0 === $this->reader->lookahead->getTag()) {
            throw new EOFError($params);
        };

        throw new SyntaxError($params);
    }

    public function _exprStmt()
    {
        $this->reader->match(Tag::T_DO);

        $expr_list = [$this->expr_parser->_expr()];

        while ($this->reader->consumeIf(',')) {
            $expr_list[] = $this->expr_parser->_expr();
        }

        return new ExprStmt($expr_list);
    }

    public function _blockStmt()
    {
        $this->reader->match(Tag::T_BEGIN);
        $body = iterator_to_array($this->_innerStmtList());
        $this->reader->match(Tag::T_END);

        return new BlockStmt($body);
    }

    public function _ifStmt()
    {
        $this->reader->match(Tag::T_IF);
        $condition = $this->expr_parser->_expr();
        $body = new StmtList(iterator_to_array($this->_innerStmtList()));
        $elif = iterator_to_array($this->_elifList());
        $else = $this->_optElse();
        $this->reader->match(Tag::T_END);

        return new IfStmt($condition, $body, $elif, $else);
    }

    public function _letStmt()
    {
        $this->reader->match(Tag::T_LET);
        $definitions = [];

        // First definition is required (without comma)
        // I could just use a goto, but, Satan would want my soul...
        $name = $this->name_parser->_identifier();

        if ($this->reader->consumeIf('::')) {
            // TODO: bind type for symbol. Currently ignored
            $type = $this->type_parser->_type();
        }

        if ($this->reader->consumeIf(':-')) {
            $value = $this->expr_parser->_expr();

            $definitions[] = [$name, $value];
        } else {
            $definitions[] = [$name, null];
        }

        while ($this->reader->consumeIf(',')) {
            $name = $this->name_parser->_identifier();

            if ($this->reader->consumeIf(':-')) {
                $value = $this->expr_parser->_expr();
                $definitions[] = [$name, $value];
            } else {
                $definitions[] = [$name, null];
            }
        }

        return new LetStmt($definitions);
    }

    public function _whileStmt()
    {
        $this->reader->match(Tag::T_WHILE);
        $condition = $this->expr_parser->_expr();
        $body = iterator_to_array($this->_innerStmtList());
        $this->reader->match(Tag::T_END);

        return new WhileStmt($condition, $body);
    }

    public function _forStmt()
    {
        $this->reader->match(Tag::T_FOR);
        $variable = $this->name_parser->_identifier();
        $this->reader->match(Tag::T_FROM);
        $from = $this->expr_parser->_expr();
        $this->reader->match(Tag::T_TO);
        $to = $this->expr_parser->_expr();
        $by = null;

        if ($this->reader->consumeIf(Tag::T_BY)) {
            $by = $this->expr_parser->_expr();
        }

        $body = new StmtList(iterator_to_array($this->_innerStmtList()));
        $this->reader->match(Tag::T_END);

        return new ForStmt($variable, $from, $to, $by, $body);
    }

    public function _foreachStmt()
    {
        $key = null;
        $by_reference = false;
        $this->reader->match(Tag::T_FOREACH);

        if ($this->reader->is(Tag::T_IDENT)) {
            $alias = $this->name_parser->_identifier();

            if ($this->reader->consumeIf(':')) {
                $key = $alias;

                $by_reference = $this->reader->consumeIf('*');
                $alias = $this->name_parser->_identifier();
            }
        } else {
            $by_reference = $this->reader->consumeIf('*');
            $alias = $this->name_parser->_identifier();
        }

        $this->reader->match(Tag::T_IN);
        $iterable = $this->expr_parser->_expr();
        $body = iterator_to_array($this->_innerStmtList());
        $this->reader->match(Tag::T_END);

        return new ForeachStmt($by_reference, $key, $alias, $iterable, $body);
    }

    public function _switchStmt()
    {
        $this->reader->match(Tag::T_SWITCH);
        $value = $this->expr_parser->_expr();
        $cases = iterator_to_array($this->_caseStmtList());
        $this->reader->match(Tag::T_END);

        return new SwitchStmt($value, $cases);
    }

    public function _tryStmt()
    {
        $this->reader->match(Tag::T_TRY);
        $body = new StmtList(iterator_to_array($this->_innerStmtList()));
        $rescues = iterator_to_array($this->_rescueStmtList());
        $finally = $this->_optFinally();
        $this->reader->match(Tag::T_END);

        return new TryStmt($body, $rescues, $finally);
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

    public function _raiseStmt()
    {
        $this->reader->match(Tag::T_RAISE);
        $expression = $this->expr_parser->_expr();

        return new RaiseStmt($expression);
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
        $stmt = $this->_innerStmt();

        return new LabelStmt($label_name, $stmt);
    }

    public function _elifList()
    {
        while ($this->reader->consumeIf(Tag::T_ELIF)) {
            $condition = $this->expr_parser->_expr();
            $body = iterator_to_array($this->_innerStmtList());
            yield new ElifStmt($condition, $body);
        }
    }

    public function _optElse()
    {
        if (!$this->reader->is(Tag::T_ELSE)) {
            return null;
        }

        $this->reader->consume();
        return new StmtList(iterator_to_array($this->_innerStmtList()));
    }

    public function _topStmt()
    {
        $decl_table = [
            Tag::T_FN     => '_fnStmt',
            Tag::T_PUB    => '_fnStmt',
            Tag::T_MODULE => '_moduleStmt',
            Tag::T_ENUM   => '_enumStmt',
            Tag::T_IMPL   => '_implStmt',
            Tag::T_CLASS  => '_classStmt',
            Tag::T_SHAPE  => '_shapeStmt'
        ];

        $meta_table = [
            Tag::T_NATIVE => '_nativeStmt',
            Tag::T_INFIXL => '_operatorStmt',
            Tag::T_INFIXR => '_operatorStmt',
            Tag::T_PREFIX => '_operatorStmt'
        ];

        $next_tag = $this->reader->lookahead->getTag();

        if (array_key_exists($next_tag, $decl_table)) {
            return call_user_func([$this->decl_parser, $decl_table[$next_tag]]);
        }

        if (array_key_exists($next_tag, $meta_table)) {
            return call_user_func([$this->meta_parser, $meta_table[$next_tag]]);
        }

        return $this->_stmt();
    }

    public function _innerStmt()
    {
        $branch_table = [
            Tag::T_FN        => '_fnStmt',
            Tag::T_ENUM      => '_enumStmt'
        ];

        $next_tag = $this->reader->lookahead->getTag();

        return array_key_exists($next_tag, $branch_table)
            ? call_user_func([$this, $branch_table[$next_tag]])
            : $this->_stmt();
    }

    public function _constStmt()
    {
        $this->reader->match(Tag::T_CONST);
        $definitions = [];

        $name = $this->name_parser->_identifier();

        if ($this->reader->consumeIf('::')) {
            // TODO: Bind type to value
            $type = $this->type_parser->_type();
        }

        $this->reader->match(':-');
        $value = $this->expr_parser->_expr();
        $definitions[] = [$name, $value];

        while ($this->reader->consumeIf(',')) {
            $name = $this->name_parser->_identifier();
            $this->reader->match(':-');
            $value = $this->expr_parser->_expr();
            $definitions[] = [$name, $value];
        }

        return new ConstStmt($definitions);
    }

    public function _parameter()
    {
        $by_reference = $this->reader->consumeIf('*');
        $name = $this->name_parser->_identifier();
        // TODO: Bind type to parameter
        if ($this->reader->consumeIf('::')) {
            $type = $this->type_parser->_type();
        }

        return (object)[
            'name'         => $name,
            'is_reference' => $by_reference
        ];
    }

    public function _caseStmtList()
    {
        $cases = [Tag::T_CASE, Tag::T_ELSE];

        while (in_array($this->reader->lookahead->getTag(), $cases, true)) {
            $is_else = $this->reader->is(Tag::T_ELSE);
            $this->reader->consume();
            $value = $is_else ? null : $this->expr_parser->_expr();
            $body = new StmtList(iterator_to_array($this->_innerStmtList()));

            yield new CaseStmt($value, $body, $is_else);
        }
    }

    public function _rescueStmtList()
    {
        while ($this->reader->consumeIf(Tag::T_RESCUE)) {
            $this->reader->match('(');
            $exception_class = $this->name_parser->_qualifiedName();
            $variable = $this->name_parser->_identifier();
            $this->reader->match(')');
            $body = new StmtList(iterator_to_array($this->_innerStmtList()));

            yield [
                "exception_class" => $exception_class,
                "variable" => $variable,
                "body" => $body
            ];
        }
    }

    public function _optFinally()
    {
        if ($this->reader->consumeIf(Tag::T_FINALLY)) {
            $body = new StmtList(iterator_to_array($this->_innerStmtList()));
            return $body;
        }

        return null;
    }

    public function _optLabel()
    {
        return $this->reader->is(Tag::T_IDENT)
            ? $this->name_parser->_identifier()
            : null;
    }
}
