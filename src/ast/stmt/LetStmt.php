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
namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Types\TypeError;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Scope\Meta;

class LetStmt extends Stmt
{
    public $name;
    public $type;
    public $value;
    public $mutable;
    private $scope;

    public function __construct($name, $type, $value, $mutable)
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
        $this->mutable = $mutable;
    }

    public function format(Parser $parser)
    {
        $source = 'let ';

        if ($this->mutable) {
            $source .= 'mut ';
        }

        $source .= $this->name;

        if (null !== $this->type) {
            $source .= ' :: ' . $this->type;
        }

        if (null !== $this->value) {
            $source .= ' :- ' . $this->value->format($parser);
        }

        $source .= PHP_EOL;
        return $source;
    }

    public function injectScope($parent_scope)
    {
        $this->scope = $parent_scope;
        $mask = Symbol::S_VARIABLE | ($this->mutable ? Symbol::S_MUTABLE : 0x0);

        if (null === $this->value) {
            $this->scope->insert($this->name, $mask);
        } else {
            $this->scope->insert($this->name, $mask | Symbol::S_INITIALIZED);
            $this->value->injectScope($parent_scope);
        }
    }

    public function runTypeChecker()
    {
        // TODO: Deal with variable referrencing itself, such as
        // let x :- x. It is "valid" in the context of a function, but not
        // in eager definition. D'oh!

        // No type, no value. Free variable
        if (null === $this->type && null === $this->value) {
            throw new TypeError(Localization::message('TYP290', [$this->name]));
        }

        if ($this->mutable) {
            $this->checkMutable();
        } else {
            $this->checkImmutable();
        }
    }

    public function checkMutable()
    {
        // No value (but we still have type)
        if (null === $this->value) {
            // Force type reduction because we still have no value to match against
            $this->type->simplify();
            $this->scope->setMeta(Meta::M_TYPE, $this->name, $this->type);
            return;
        }

        // No type (but we still have the value)
        if (null === $this->type) {
            $inferred_type = $this->value->getType();
            $this->scope->setMeta(Meta::M_TYPE, $this->name, $inferred_type);
            return;
        }

        // Got type and value
        $this->checkTypeAndValue();
    }

    public function checkImmutable()
    {
        // No value on immutable variable (error)
        if (null === $this->value) {
            throw new TypeError(Localization::message('TYP270', [$this->name . ' :: ' . $this->type]));
        }

        // No type declared. The compiler will infer
        if (null === $this->type) {
            $type = $this->value->getType();
            $this->scope->setMeta(Meta::M_TYPE, $this->name, $type);
            return;
        }

        $this->checkTypeAndValue();
    }

    public function checkTypeAndValue()
    {
        // Type and value exist. Check them!
        $this->scope->setMeta(Meta::M_TYPE, $this->name, $this->type);
        $inferred_type = $this->value->getType();
        if (!$this->type->check($inferred_type)) {
            throw new TypeError(Localization::message('TYP300', [
                $this->name, $this->type, $inferred_type
            ]));
        }
    }
}
