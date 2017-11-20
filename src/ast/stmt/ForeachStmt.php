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

use \QuackCompiler\Ast\Types\ListType;
use \QuackCompiler\Ast\Types\MapType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\TypeError;

class ForeachStmt extends Stmt
{
    public $key;
    public $alias;
    public $generator;
    public $body;

    public function __construct($key, $alias, $generator, $body)
    {
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
            $source .= ': ';
        }

        $source .= $this->alias;

        $source .= ' in ';
        $source .= $this->generator->format($parser);
        $source .= PHP_EOL;
        $source .= $this->body->format($parser);
        $source .= $parser->indent();
        $source .= 'end';
        $source .= PHP_EOL;

        return $source;
    }

    public function injectScope($parent_scope)
    {
        $this->scope = new Scope($parent_scope);
        $this->scope->setMetaInContext(Meta::M_LABEL, Meta::nextMetaLabel());

        // Pre-inject key and value in block scope
        if (null !== $this->key) {
            $this->scope->insert($this->key, Symbol::S_VARIABLE | Symbol::S_INITIALIZED);
        }

        if ($this->key === $this->alias) {
            throw new ScopeError(Localization::message('SCO180', [$this->alias]));
        }

        $this->scope->insert($this->alias, Symbol::S_VARIABLE | Symbol::S_INITIALIZED | Symbol::S_MUTABLE);
        $this->generator->injectScope($parent_scope);
        $this->body->injectScope($this->scope);
    }

    public function runTypeChecker()
    {
        // The following type-rules are applicable:
        // List :: ∀ a. Int -> a
        // Map :: ∀ a b. a -> b
        $generator_type = $this->generator->getType();

        // When the element is not iterable
        if (!$generator_type->isIterable()) {
            throw new TypeError(Localization::message('TYP260', [$generator_type]));
        }

        if (null !== $this->key) {
            $key_type = $generator_type instanceof ListType
                ? $this->scope->getPrimitiveType('Number')
                : $generator_type->key;
            $this->scope->setMeta(Meta::M_TYPE, $this->key, $key_type);
        }

        $value_type = $generator_type instanceof ListType
            ? $generator_type->type
            : $generator_type->value;
        $this->scope->setMeta(Meta::M_TYPE, $this->alias, $value_type);
        $this->body->runTypeChecker();
    }
}
