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
namespace QuackCompiler\Ast\Decl;

use \QuackCompiler\Ast\Decl;
use \QuackCompiler\Ds\Set;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Types\TypeError;
use \QuackCompiler\Types\Unification;

class LetDecl implements Decl
{
    public $name;
    public $type;
    public $value;
    public $recursive;
    public $mutable;

    public function __construct($name, $type, $value, $recursive, $mutable)
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
        $this->recursive = $recursive;
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
            $source .= ' :: ' . $this->type->format($parser);
        }

        $source .= ' :- ' . $this->value->format($parser) . PHP_EOL;
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

    public function runTypeChecker(Scope $scope, Set $non_generic)
    {
        if ($this->mutable) {
            $this->checkMutable($scope, $non_generic);
        } else {
            $this->checkImmutable($scope, $non_generic);
        }
    }

    public function checkMutable($scope, $non_generic)
    {
        // No type (but we still have the value)
        if (null === $this->type) {
            $inferred_type = $this->value->getType();
            $this->scope->setMeta(Meta::M_TYPE, $this->name, $inferred_type);
            return;
        }

        // Got type and value
        $this->checkTypeAndValue($scope, $non_generic);
    }

    public function checkImmutable($scope, $non_generic)
    {
        // No type declared. The compiler will infer
        if (null === $this->type) {
            $type = $this->value->analyze($scope, $non_generic);
            $this->scope->setMeta(Meta::M_TYPE, $this->name, $type);
            return;
        }

        $type = $this->type->compute($scope);
        $this->scope->setMeta(Meta::M_TYPE, $this->name, $type);
        $this->checkTypeAndValue($scope, $non_generic);
    }

    public function checkTypeAndValue($scope, $non_generic)
    {
        $expected_type = $this->type->compute($scope);
        $inferred_type = $this->value->analyze($scope, $non_generic);

        try {
            Unification::unify($expected_type, $inferred_type);
        } catch (TypeError $error) {
            throw new TypeError(Localization::message('TYP300', [
                $this->name, $expected_type, $inferred_type
            ]));
        }
    }
}
