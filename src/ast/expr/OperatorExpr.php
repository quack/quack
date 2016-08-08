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

        // Type checker rules. TODO: Isolate as a function. `getType` should do it. Seriously!
        if (in_array($this->operator, ['+', '-', '*', '/'], true)) {
            $type = (object)[
                'left'  => $this->left->getType(),
                'right' => $this->right->getType()
            ];

            $is_valid_num = function ($type) {
                return $type->isType(NativeQuackType::T_INT) || $type->isType(NativeQuackType::T_DOUBLE);
            };

            if (!$is_valid_num($type->left)) {
                // TODO: Should throw type error (create /src/types/TypeError.php)
                throw new ScopeError([
                    'message' => "TypeError: left operand of `{$this->operator}' not a number. Got {$type->left}"
                ]);
            }

            if (!$is_valid_num($type->right)) {
                throw new ScopeError([
                    'message' => "TypeError: right operand of `{$this->operator}' not a number. Got {$type->right}"
                ]);
            }
        }
    }

    public function getType()
    {
        return new Type(max($this->left->getType()->code, $this->right->getType()->code));
    }
}
