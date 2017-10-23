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

use \QuackCompiler\Ast\Types\ListType;
use \QuackCompiler\Ast\Types\LiteralType;
use \QuackCompiler\Ast\Types\MapType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\NativeQuackType;
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

    public function injectScope($parent_scope)
    {
        $this->scope = new Scope($parent_scope);
        $this->scope->setMetaInContext(Meta::M_LABEL, Meta::nextMetaLabel());

        // Pre-inject key and value in block scope
        if (null !== $this->key) {
            $this->scope->insert($this->key, Kind::K_VARIABLE | Kind::K_INITIALIZED);
        }

        if ($this->key === $this->alias) {
            throw new ScopeError(Localization::message('SCO180', [$this->alias]));
        }

        $this->scope->insert($this->alias, Kind::K_VARIABLE | Kind::K_INITIALIZED | Kind::K_MUTABLE);
        $this->generator->injectScope($parent_scope);

        foreach ($this->body as $node) {
            $node->injectScope($this->scope);
        }
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
                ? new LiteralType(NativeQuackType::T_NUMBER)
                : $generator_type->key;
            $this->scope->setMeta(Meta::M_TYPE, $this->key, $key_type);
        }

        $value_type = $generator_type instanceof ListType
            ? $generator_type->type
            : $generator_type->key;

        foreach ($this->body as $stmt) {
            $stmt->runTypeChecker();
        }
    }
}
