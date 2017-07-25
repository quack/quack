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
namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Ast\Types\LiteralType;
use \QuackCompiler\Ast\Types\ObjectType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\NativeQuackType;
use \QuackCompiler\Types\TypeError;

class OperatorExpr extends Expr
{
    public $left;
    public $operator;
    public $right;
    private $scoperef;

    public function __construct(Expr $left, $operator, $right)
    {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    private function isMemberAccess()
    {
        return '.' === $this->operator;
    }

    public function format(Parser $parser)
    {
        $blanks = $this->isMemberAccess() ? '' : ' ';

        $source = $this->left->format($parser);
        $source .= $blanks;
        $source .= Tag::getOperatorLexeme($this->operator);
        $source .= $blanks;
        $source .= $this->isMemberAccess() ? $this->right : $this->right->format($parser);

        return $this->parenthesize($source);
    }

    public function injectScope(&$parent_scope)
    {
        $this->scoperef = &$parent_scope;
        $this->left->injectScope($parent_scope);

        if (!$this->isMemberAccess()) {
            $this->right->injectScope($parent_scope);
        }

        if (':-' === $this->operator) {
            if ($this->left instanceof NameExpr) {
                $symbol = $parent_scope->lookup($this->left->name);

                // When symbol is not a variable
                if (~$symbol & Kind::K_VARIABLE) {
                    throw new ScopeError(Localization::message('SCO070', [$this->left->name]));
                }

                // When symbol is not mutable
                if (~$symbol & Kind::K_MUTABLE) {
                    throw new ScopeError(Localization::message('SCO080', [$this->left->name]));
                }
            } else {
                // We have a range of specific nodes that are allowed
                $valid_assignment = $this->left instanceof AccessExpr ||
                    $this->left instanceof ArrayExpr; // Array destructuring

                if (!$valid_assignment) {
                    throw new ScopeError(Localization::message('SCO090', []));
                }

                // When it is array destructuring, ensure all the subnodes are names
                // TODO: Implement destructuring on let, because this is currently useless
                if ($this->left instanceof ArrayExpr) {
                    foreach ($this->left->items as $item) {
                        if (!($item instanceof NameExpr)) {
                            throw new ScopeError(Localization::message('SCO100', []));
                        }
                    }
                }
            }
        }
    }

    public function getType()
    {
        $type = (object)[
            'left'  => $this->left->getType(),
            'right' => 'string' === gettype($this->right) ? $this->right : $this->right->getType()
        ];

        $op_name = Tag::getOperatorLexeme($this->operator);

        if ('.' === $this->operator) {
            // When member access and the property exists on the left type
            if ($type->left instanceof ObjectType && isset($type->left->properties[$this->right])) {
                return $type->left->properties[$this->right];
            }

            throw new TypeError(Localization::message('TYP090', [$type->left, $type->right]));
        }

        // Type-checking for assignment. Don't worry. Left-hand assignment was handled on
        // scope injection
        if (':-' === $this->operator) {
            // When right side cannot be attributed to left side
            if (!$type->left->check($type->right)) {
                $target = $this->left instanceof NameExpr
                    ? "`{$this->left->name}' :: {$type->left}"
                    : $type->right;

                throw new TypeError(Localization::message('TYP100', [$type->right, $target]));
            }

            return $type->left;
        }

        // Type checking for numeric and string concat operations
        $numeric_op = ['+', '-', '*', '**', '/', '>>', '<<', '^', '&', '|', Tag::T_MOD];
        if (in_array($this->operator, $numeric_op, true)) {
            if ('+' === $this->operator && $type->left->isString() && $type->right->isString()) {
                return new LiteralType(NativeQuackType::T_STR);
            }

            if ($type->left->isNumber() && $type->right->isNumber()) {
                return new LiteralType(NativeQuackType::T_NUMBER);
            }

            throw new TypeError(Localization::message('TYP110', [$op_name, $type->left, $op_name, $type->right]));
        }

        // Null coalesce operator
        if ('??' === $this->operator) {
            if (!$type->left->check($type->right)) {
                throw new TypeError(Localization::message('TYP120', [$type->left, $type->right]));
            }

            return $type->left;
        }

        // Type checking for equality operators and coalescence
        $eq_op = ['=', '<>', '>', '>=', '<', '<='];
        if (in_array($this->operator, $eq_op, true)) {
            if (!$type->left->check($type->right)) {
                throw new TypeError(Localization::message('TYP130', [$type->left, $op_name, $type->right]));
            }

            return new LiteralType(NativeQuackType::T_BOOL);
        }

        // Type checking for string matched by regex
        if ('=~' === $this->operator) {
            if (!$type->left->isString() || !$type->right->isRegex()) {
                throw new TypeError(Localization::message('TYP110', [$op_name, $type->left, $op_name, $type->right]));
            }

            return new LiteralType(NativeQuackType::T_BOOL);
        }

        // Boolean algebra
        $bool_op = [Tag::T_AND, Tag::T_OR, Tag::T_XOR];
        if (in_array($this->operator, $bool_op, true)) {
            if (!$type->left->isBoolean() || !$type->right->isBoolean()) {
                throw new TypeError(Localization::message('TYP110', [$op_name, $type->left, $op_name, $type->right]));
            }

            return new LiteralType(NativeQuackType::T_BOOL);
        }
    }
}
