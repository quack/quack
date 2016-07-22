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

use \QuackCompiler\Ast\Expr\ArrayPairExpr;

use \QuackCompiler\Ast\Stmt\BlockStmt;
use \QuackCompiler\Ast\Stmt\BreakStmt;
use \QuackCompiler\Ast\Stmt\CaseStmt;
use \QuackCompiler\Ast\Stmt\BlueprintStmt;
use \QuackCompiler\Ast\Stmt\ExtensionStmt;
use \QuackCompiler\Ast\Stmt\ConstStmt;
use \QuackCompiler\Ast\Stmt\ContinueStmt;
use \QuackCompiler\Ast\Stmt\FnStmt;
use \QuackCompiler\Ast\Stmt\DoWhileStmt;
use \QuackCompiler\Ast\Stmt\ElifStmt;
use \QuackCompiler\Ast\Stmt\EnumStmt;
use \QuackCompiler\Ast\Stmt\ExprStmt;
use \QuackCompiler\Ast\Stmt\ForeachStmt;
use \QuackCompiler\Ast\Stmt\ForStmt;
use \QuackCompiler\Ast\Stmt\IfStmt;
use \QuackCompiler\Ast\Stmt\LabelStmt;
use \QuackCompiler\Ast\Stmt\LetStmt;
use \QuackCompiler\Ast\Stmt\ModuleStmt;
use \QuackCompiler\Ast\Stmt\OpenStmt;
use \QuackCompiler\Ast\Stmt\PrintStmt;
use \QuackCompiler\Ast\Stmt\MemberStmt;
use \QuackCompiler\Ast\Stmt\RaiseStmt;
use \QuackCompiler\Ast\Stmt\ReturnStmt;
use \QuackCompiler\Ast\Stmt\SwitchStmt;
use \QuackCompiler\Ast\Stmt\TryStmt;
use \QuackCompiler\Ast\Stmt\WhileStmt;

use \QuackCompiler\Ast\Expr\PrefixExpr;

class Grammar
{
    public $parser;
    public $checker;

    public function __construct(TokenReader $parser)
    {
        $this->parser = $parser;
        $this->checker = new TokenChecker($parser);
    }

    public function start()
    {
        return iterator_to_array($this->_topStmtList());
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

    public function _blueprintStmtList()
    {
        while (!$this->checker->isEoF()) {
            switch ($this->parser->lookahead->getTag()) {
                case Tag::T_FN:
                case Tag::T_CONST:
                case Tag::T_OPEN:
                case Tag::T_MEMBER:
                    yield $this->_blueprintStmt();
                    continue 2;
                default:
                    break 2;
            }
        }
    }

    public function _arrayPairList()
    {
        while (!$this->parser->is('}')) {
            $left = $this->_expr();
            $right = null;

            if ($this->parser->is('->')) {
                $this->parser->consume();
                $right = $this->_expr();
            }

            if (!$this->parser->is('}')) {
                $this->parser->match(';');
            }

            yield new ArrayPairExpr($left, $right);
        }

        $this->parser->match('}');
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
            ':-'            => '_labelStmt'
        ];

        foreach ($branch_table as $token => $action) {
            if ($this->parser->is($token)) {
                $first_class_stmt = $this->{$action}();

                // Syntactic sugar to allow post-conditionals for statements
                if ($this->parser->is(Tag::T_WHEN) || $this->parser->is(Tag::T_UNLESS)) {
                    $lexeme = $this->parser->consumeAndFetch()->lexeme;
                    $condition = $this->_expr();

                    return new IfStmt(
                        $lexeme !== 'unless'
                            ? $condition
                            : new PrefixExpr(new Token(Tag::T_NOT), $condition),
                        $first_class_stmt,
                        [],
                        null
                    );
                }

                return $first_class_stmt;
            }
        }

        throw new SyntaxError([
            'expected' => 'statement',
            'found'    => $this->parser->lookahead,
            'parser'   => $this->parser
        ]);
    }

    public function _exprStmt()
    {
        $this->parser->match(Tag::T_DO);

        $expr_list = [$this->_expr()];

        while ($this->parser->is(',')) {
            $this->parser->consume();
            $expr_list[] = $this->_expr();
        }

        return new ExprStmt($expr_list);
    }

    public function _blockStmt()
    {
        $this->parser->match(Tag::T_BEGIN);
        $body = iterator_to_array($this->_innerStmtList());
        $this->parser->match(Tag::T_END);

        return new BlockStmt($body);
    }

    public function _ifStmt()
    {
        $this->parser->match(Tag::T_IF);
        $condition = $this->_expr();
        $body = iterator_to_array($this->_innerStmtList());
        $elif = iterator_to_array($this->_elifList());
        $else = $this->_optElse();
        $this->parser->match(Tag::T_END);

        return new IfStmt($condition, $body, $elif, $else);
    }

    public function _letStmt()
    {
        $this->parser->match(Tag::T_LET);
        $definitions = [];

        // First definition is required (without comma)
        // I could just use a goto, but, Satan would want my soul...
        $name = $this->identifier();
        $this->parser->match(':-');
        $value = $this->_expr();
        $definitions[$name] = $value;

        while ($this->parser->is(',')) {
            $this->parser->consume();
            $name = $this->identifier();
            $this->parser->match(':-');
            $value = $this->_expr();
            $definitions[$name] = $value;
        }

        return new LetStmt($definitions);
    }

    public function _whileStmt()
    {
        $this->parser->match(Tag::T_WHILE);
        $condition = $this->_expr();
        $body = iterator_to_array($this->_innerStmtList());
        $this->parser->match(Tag::T_END);

        return new WhileStmt($condition, $body);
    }

    public function _forStmt()
    {
        $this->parser->match(Tag::T_FOR);
        $variable = $this->identifier();
        $this->parser->match(Tag::T_FROM);
        $from = $this->_expr();
        $this->parser->match(Tag::T_TO);
        $to = $this->_expr();
        $by = null;

        if ($this->parser->is(Tag::T_BY)) {
            $this->parser->consume();
            $by = $this->_expr();
        }

        $body = iterator_to_array($this->_innerStmtList());
        $this->parser->match(Tag::T_END);

        return new ForStmt($variable, $from, $to, $by, $body);
    }

    public function _foreachStmt()
    {
        $key = null;
        $by_reference = false;
        $this->parser->match(Tag::T_FOREACH);

        if ($this->parser->is(Tag::T_IDENT)) {
            $alias = $this->identifier();

            if ($this->parser->is('->')) {
                $this->parser->consume();
                $key = $alias;

                ($by_reference = $this->parser->is('*')) && /* then */ $this->parser->consume();
                $alias = $this->identifier();
            }
        } else {
            ($by_reference = $this->parser->is('*')) && /* then */ $this->parser->consume();
            $alias = $this->identifier();
        }

        $this->parser->match(Tag::T_IN);
        $iterable = $this->_expr();
        $body = iterator_to_array($this->_innerStmtList());
        $this->parser->match(Tag::T_END);

        return new ForeachStmt($by_reference, $key, $alias, $iterable, $body);
    }

    public function _switchStmt()
    {
        $this->parser->match(Tag::T_SWITCH);
        $value = $this->_expr();
        $cases = iterator_to_array($this->_caseStmtList());
        $this->parser->match(Tag::T_END);

        return new SwitchStmt($value, $cases);
    }

    public function _tryStmt()
    {
        $this->parser->match(Tag::T_TRY);
        $body = iterator_to_array($this->_innerStmtList());
        $rescues = iterator_to_array($this->_rescueStmtList());
        $finally = $this->_optFinally();
        $this->parser->match(Tag::T_END);

        return new TryStmt($body, $rescues, $finally);
    }

    public function _breakStmt()
    {
        $this->parser->match(Tag::T_BREAK);
        $expression = $this->_optExpr();
        return new BreakStmt($expression);
    }

    public function _continueStmt()
    {
        $this->parser->match(Tag::T_CONTINUE);
        $expression = $this->_optExpr();
        return new ContinueStmt($expression);
    }

    public function _raiseStmt()
    {
        $this->parser->match(Tag::T_RAISE);
        $expression = $this->_expr();

        return new RaiseStmt($expression);
    }

    public function _returnStmt()
    {
        $this->parser->match('^');
        $expression = $this->_optExpr();

        return new ReturnStmt($expression);
    }

    public function _labelStmt()
    {
        $this->parser->match(':-');
        $label_name = $this->identifier();

        return new LabelStmt($label_name);
    }

    public function _elifList()
    {
        while ($this->parser->is(Tag::T_ELIF)) {
            $this->parser->consume();
            $condition = $this->_expr();
            $body = iterator_to_array($this->_innerStmtList());
            yield new ElifStmt($condition, $body);
        }
    }

    public function _optElse()
    {
        if (!$this->parser->is(Tag::T_ELSE)) {
            return null;
        }

        $this->parser->consume();
        return $this->_stmt();
    }

    public function _topStmt()
    {
        $branch_table = [
            Tag::T_BLUEPRINT => '_blueprintDeclStmt',
            Tag::T_FN        => '_fnStmt',
            Tag::T_MODULE    => '_moduleStmt',
            Tag::T_OPEN      => '_openStmt',
            Tag::T_ENUM      => '_enumStmt',
            Tag::T_EXTENSION => '_extensionDeclStmt'
        ];

        $next_tag = $this->parser->lookahead->getTag();

        return array_key_exists($next_tag, $branch_table)
            ? call_user_func([$this, $branch_table[$next_tag]])
            : $this->_stmt();
    }

    public function _innerStmt()
    {
        $branch_table = [
            Tag::T_FN        => '_fnStmt',
            Tag::T_BLUEPRINT => '_blueprintDeclStmt',
            Tag::T_ENUM      => '_enumStmt'
        ];

        $next_tag = $this->parser->lookahead->getTag();

        return array_key_exists($next_tag, $branch_table)
            ? call_user_func([$this, $branch_table[$next_tag]])
            : $this->_stmt();
    }

    public function _blueprintStmt()
    {
        $branch_table = [
            Tag::T_OPEN   => '_openStmt',
            Tag::T_FN     => '_fnStmt',
            Tag::T_MEMBER => '_memberStmt'
        ];

        return call_user_func([
            $this,
            $branch_table[$this->parser->lookahead->getTag()]
        ]);
    }

    public function _memberStmt()
    {
        $this->parser->match(Tag::T_MEMBER);
        $definitions = [];

        $name = $this->identifier();
        $value = null;

        if ($this->parser->is(':-')) {
            $this->parser->consume();
            $value = $this->_expr();
        }

        $definitions[$name] = $value;

        while ($this->parser->is(',')) {
            $this->parser->consume();
            $name = $this->identifier();
            $value = null;

            if ($this->parser->is(':-')) {
                $this->parser->consume();
                $value = $this->_expr();
            }

            $definitions[$name] = $value;
        }

        return new MemberStmt($definitions);
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

    public function _extensionDeclStmt()
    {
        $appliesTo = [];
        $appliesToRegexes = [];
        $implements = [];

        $this->parser->match(Tag::T_EXTENSION);
        $this->parser->match(Tag::T_FOR);

        do {
            if ($this->parser->is(Tag::T_REGEX)) {
                $appliesToRegexes[] = $this->parser->consumeAndFetch();
            } else {
                $appliesTo[] = $this->qualifiedName();
            }

            if ($this->parser->is(';')) {
                $this->parser->consume();
            } else {
                break;
            }
        } while (true);

        if ($this->parser->is('#')) {
            do {
                $this->parser->consume();
                $implements[] = $this->qualifiedName();
            } while ($this->parser->is(';'));
        }

        $body = iterator_to_array($this->_blueprintStmtList());
        $this->parser->match(Tag::T_END);

        return new ExtensionStmt($appliesTo, $appliesToRegexes, $implements, $body);
    }

    public function _blueprintDeclStmt()
    {
        $extends = null;
        $implements = [];

        $this->parser->match(Tag::T_BLUEPRINT);
        $blueprint_name = $this->identifier();

        if ($this->parser->is(':')) {
            $this->parser->consume();
            $extends = $this->qualifiedName();
        }

        if ($this->parser->is('#')) {
            do {
                $this->parser->consume();
                $implements[] = $this->qualifiedName();
            } while ($this->parser->is(';'));
        }

        $body = iterator_to_array($this->_blueprintStmtList());
        $this->parser->match(Tag::T_END);

        return new BlueprintStmt($blueprint_name, $extends, $implements, $body);
    }

    public function _fnStmt()
    {
        $by_reference = false;

        $this->parser->match(Tag::T_FN);

        if ($this->parser->is('*')) {
            $this->parser->consume();
            $by_reference = true;
        }

        $name = $this->identifier();
        $parameters = $this->_parameters();

        $body = iterator_to_array($this->_innerStmtList());
        $this->parser->match(Tag::T_END);

        return new FnStmt($name, $by_reference, $body, $parameters);
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

    public function _constStmt()
    {
        $this->parser->match(Tag::T_CONST);
        $definitions = [];

        $name = $this->identifier();
        $this->parser->match(':-');
        $value = $this->_expr();
        $definitions[$name] = $value;

        while ($this->parser->is(',')) {
            $this->parser->consume();
            $name = $this->identifier();
            $this->parser->match(':-');
            $value = $this->_expr();
            $definitions[$name] = $value;
        }

        return new ConstStmt($definitions);
    }

    public function _parameters()
    {
        $parameters = [];

        if ($this->parser->is('!')) {
            $this->parser->consume();
            return $parameters;
        }

        $this->parser->match('[');

        while (!$this->parser->is(']')) {
            $parameters[] = $this->_parameter();

            if ($this->parser->is(';')) {
                $this->parser->consume();
            } else {
                break;
            }
        }

        $this->parser->match(']');
        return $parameters;
    }

    public function _parameter()
    {
        $ellipsis = false;
        $by_reference = false;

        if ($ellipsis = $this->parser->is('...')) {
            $this->parser->consume();
        }

        if ($by_reference = $this->parser->is('*')) {
            $this->parser->consume();
        }

        $name = $this->identifier();

        return [
            'name' => $name,
            'by_reference' => $by_reference,
            'ellipsis' => $ellipsis
        ];
    }

    public function _caseStmtList()
    {
        $cases = [Tag::T_CASE, Tag::T_ELSE];

        while (in_array($this->parser->lookahead->getTag(), $cases, true)) {
            $is_else = $this->parser->is(Tag::T_ELSE);
            $this->parser->consume();
            $value = $is_else ? null : $this->_expr();
            $body = iterator_to_array($this->_innerStmtList());

            yield new CaseStmt($value, $body, $is_else);
        }
    }

    public function _rescueStmtList()
    {
        while ($this->parser->is(Tag::T_RESCUE)) {
            $this->parser->consume();
            $this->parser->match('[');
            $exception_class = $this->qualifiedName();
            $variable = $this->identifier();
            $this->parser->match(']');
            $body = iterator_to_array($this->_innerStmtList());

            yield [
                "exception_class" => $exception_class,
                "variable" => $variable,
                "body" => $body
            ];
        }
    }

    public function _optFinally()
    {
        if ($this->parser->is(Tag::T_FINALLY)) {
            $this->parser->consume();
            $body = iterator_to_array($this->_innerStmtList());
            return $body;
        }

        return null;
    }

    public function _name()
    {
        $name = $this->parser->lookahead;
        $this->parser->match(Tag::T_IDENT);
        return $name;
    }

    public function _optExpr()
    {
        return $this->_expr(0, true);
    }

    public function _expr($precedence = 0, $opt = false)
    {
        $token = $this->parser->lookahead;
        $prefix = $this->parser->prefixParseletForToken($token);

        if (is_null($prefix)) {
            if (!$opt) {
                throw new SyntaxError([
                    'expected' => 'expression',
                    'found'    => $token,
                    'parser'   => $this->parser
                ]);
            }

            return null;
        }

        // We consume the token only when ensure it has a parselet, thus,
        // avoiding to rollback in the tape
        $this->parser->consume();
        $left = $prefix->parse($this, $token);

        // Where clause
        if ($this->parser->is(Tag::T_WHERE)) {
            $this->appendWhere($left);
        }

        while ($precedence < $this->getPrecedence()) {
            $token = $this->parser->consumeAndFetch();
            $infix = $this->parser->infixParseletForToken($token);
            $left = $infix->parse($this, $left, $token);
        }

        return $left;
    }

    private function getPrecedence()
    {
        $parser = $this->parser->infixParseletForToken($this->parser->lookahead);
        return !is_null($parser)
            ? $parser->getPrecedence()
            : 0;
    }

    /* Coproductions */
    public function appendWhere(&$expr)
    {
        $this->parser->match(Tag::T_WHERE);
        $where = [];

        $name = $this->identifier();
        $this->parser->match(':-');
        $value = $this->_expr();
        $where[$name] = $value;

        while ($this->parser->is(',')) {
            $this->parser->consume();
            $name = $this->identifier();
            $this->parser->match(':-');
            $value = $this->_expr();
            $where[$name] = $value;
        }

        $this->parser->match(Tag::T_END);

        $expr->where = $where;
    }

    public function qualifiedName()
    {
        $symbol_pointers = [$this->parser->match(Tag::T_IDENT)];
        while ($this->parser->is('.')) {
            $this->parser->consume();
            $symbol_pointers[] = $this->parser->match(Tag::T_IDENT);
        }

        return array_map(function ($name) {
            return $this->parser->resolveScope($name);
        }, $symbol_pointers);
    }

    public function identifier()
    {
        return $this->parser->resolveScope($this->parser->match(Tag::T_IDENT));
    }
}
