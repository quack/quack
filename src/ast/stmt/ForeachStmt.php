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
namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Ast\Util;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\NativeQuackType;
use \QuackCompiler\Types\Type;

class ForeachStmt extends Stmt
{
    public $by_reference;
    public $key;
    public $alias;
    public $generator;
    public $body;

    public function __construct($by_reference, $key, $alias, $generator, $body)
    {
        $this->by_reference = $by_reference;
        $this->key = $key;
        $this->alias = $alias;
        $this->generator = $generator;
        $this->body = $body;
    }

    public function format(Parser $parser)
    {
        $source = 'foreach ';

        if (null !== $this->key) {
            $source .= $this->key;
            $source .= ' -> ';
        }

        if ($this->by_reference) {
            $source .= '*';
        }

        $source .= $this->alias;

        $source .= ' in ';
        $source .= $this->generator->format($parser);
        $source .= PHP_EOL;

        $parser->openScope();

        foreach ($this->body as $stmt) {
            $source .= $parser->indent();
            $source .= $stmt->format($parser);
        }

        $parser->closeScope();

        $source .= $parser->indent();
        $source .= 'end';
        $source .= PHP_EOL;

        return $source;
    }

    public function injectScope(&$parent_scope)
    {
        $this->createScopeWithParent($parent_scope);

        // Pre-inject key and value in block scope
        if (null !== $this->key) {
            $this->scope->insert($this->key, Kind::K_VARIABLE | Kind::K_INITIALIZED);
        }

        if ($this->key === $this->alias) {
            throw new ScopeError([
                'message' => "Same key and value variable name " .
                             "(`{$this->alias}') supplied for foreach"
            ]);
        }

        $this->scope->insert($this->alias, Kind::K_VARIABLE | Kind::K_INITIALIZED | Kind::K_MUTABLE);
        $this->bindDeclarations($this->body);
        $this->generator->injectScope($parent_scope);

        foreach ($this->body as $node) {
            $node->injectScope($this->scope);
        }
    }

    public function runTypeChecker()
    {
        // The following type-rules are applicable:
        // List { key -> value } = ∀ a. List { Int -> a' }
        // Map { key -> value } = ∀ a b. Map { a' -> b' }
        $generator_type = $this->generator->getType();

        // When the element has no subtype to be an iterable
        if (null === $generator_type->subtype)  {
            throw new ScopeError([
                'message' => "`{$generator_type}' is not iterable"
            ]);
        }

        // When the element has no deducible subtype (list)
        if (!is_array($generator_type->subtype) && NativeQuackType::T_LAZY === $generator_type->subtype->code) {
            throw new ScopeError([
                'message' => "Undeducible subtype `{$generator_type->subtype}' of `{$generator_type}'"
            ]);
        }

        // When the element has no deducible subtype (map)
        if (is_array($generator_type->subtype) && in_array(NativeQuackType::T_LAZY, [
            $generator_type->subtype['key']->code,
            $generator_type->subtype['value']->code
        ], true)) {
            throw new ScopeError([
                'message' => "Undeducible `Map' subtype in `{$generator_type}'"
            ]);
        }

        if (null !== $this->key) {
            $this->scope->setMeta(Meta::M_TYPE, $this->key, $generator_type->isList()
                ? new Type(NativeQuackType::T_NUMBER)
                : clone $generator_type->subtype['key']
            );
        }

        $this->scope->setMeta(Meta::M_TYPE, $this->alias, $generator_type->isList()
            ? clone $generator_type->subtype
            : clone $generator_type->subtype['value']
        );

        foreach ($this->body as $stmt) {
            $stmt->runTypeChecker();
        }
    }
}
