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

use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\NativeQuackType;
use \QuackCompiler\Types\Type;

class OperatorExpr extends Expr
{
    public $left;
    public $operator;
    public $right;

    public function __construct(Expr $left, $operator, $right)
    {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    private function isMemberAccess()
    {
        return '.' === $this->operator || '?.' === $this->operator;
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
        $this->left->injectScope($parent_scope);

        if (!$this->isMemberAccess()) {
            $this->right->injectScope($parent_scope);
        }

        if (':-' === $this->operator) {
            if ($this->left instanceof NameExpr) {
                // When it is an attribution by name, ensure the variable is mutable
                $symbol = $parent_scope->lookup($this->left->name);

                if (!($symbol & Kind::K_MUTABLE)) {
                    throw new ScopeError([
                        'message' => "Symbol `{$this->left->name}' is immutable"
                    ]);
                }
            } else {
                // We have a range of specific nodes that are allowed
                $valid_assignment = $this->left instanceof AccessExpr ||
                    $this->left instanceof ArrayExpr; // Array destructuring

                if (!$valid_assignment) {
                    throw new ScopeError([
                        'message' => "Invalid left-hand side in assignment"
                    ]);
                }

                // When it is array destructuring, ensure all the subnodes are names
                if ($this->left instanceof ArrayExpr) {
                    foreach ($this->left->items as $item) {
                        if (!($item instanceof NameExpr)) {
                            throw new ScopeError([
                                'message' => "Array destructuring expects all children to be names"
                            ]);
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
            'right' => $this->right->getType()
        ];

        $op_name = Tag::getOperatorLexeme($this->operator);

        // Type checking for numeric and string concat operations
        $numeric_op = ['+', '-', '*', '**', '/', '>>', '<<', '>=', '<=', Tag::T_MOD];
        if (in_array($this->operator, $numeric_op, true)) {

            if ('+' === $this->operator && $type->left->isString() && $type->right->isString()) {
                return new Type(NativeQuackType::T_STR);
            }

            if ($type->left->isNumber() && $type->right->isNumber()) {
                return new Type(max($type->left->code, $type->right->code));
            }

            throw new ScopeError([
                'message' => "No type overload found for operator `{$op_name}' at " .
                             "{{$type->left} {$op_name} {$type->right}}"
            ]);
        }

        // Type checking for equality operators
        $eq_op = ['=', '<>'];
        if (in_array($this->operator, $eq_op, true)) {

            if ($type->left === $type->right || ($type->left->isNumber() && $type->right->isNumber())) {
                return new Type(NativeQuackType::T_BOOL);
            }

            throw new ScopeError([
                'message' => "Why in the world are you trying to compare two expressions of different types? at " .
                             "{{$type->left} {$op_name} {$type->right}}"
            ]);
        }

        // Type checking for string matched by regex
        if ('=~' === $this->operator) {
            if (!$type->left->isString() || !$type->right->isRegex()) {
                throw new ScopeError([
                    'message' => "No type overload found for operator `=~' at " .
                                 "{{$type->left} =~ {$type->right}}"
                ]);
            }
        }

        // Boolean algebra
        $bool_op = [Tag::T_AND, Tag::T_OR, Tag::T_XOR];
        if (in_array($this->operator, $bool_op, true)) {
            if (!$type->left->isBoolean() || !$type->right->isBoolean()) {
                throw new ScopeError([
                    'message' => "No type overload found for operator `{$op_name}' " .
                                 "{{$type->left} {$op_name} {$type->right}}"
                ]);
            }

            return new Type(NativeQuackType::T_BOOL);
        }
    }
}
