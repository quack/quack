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
use \QuackCompiler\Ast\Types\MapType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\NativeQuackType;
use \QuackCompiler\Types\Type;
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

    public function injectScope(&$parent_scope)
    {
        $this->createScopeWithParent($parent_scope);
        $this->scope->setMetaInContext(Meta::M_LABEL, Meta::nextMetaLabel());

        // Pre-inject key and value in block scope
        if (null !== $this->key) {
            $this->scope->insert($this->key, Kind::K_VARIABLE | Kind::K_INITIALIZED);
        }

        if ($this->key === $this->alias) {
            throw new ScopeError(Localization::message('SCO180', [$this->alias]));
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

        // When the element is not iterable
        if (!$generator_type->isIterable()) {
            throw new TypeError(Localization::message('TYP260', [$generator_type]));
        }

        // When the element has no deducible subtype (list)
        if ($generator_type->isList() && $generator_type->subtype->isLazy()) {
            throw new TypeError(Localization::message('TYP270', [$generator_type->subtype, $generator_type]));
        }

        // When the element has no deducible subtype (map)
        if (is_array($generator_type->subtype)
            && ($generator_type->subtype['key']->isLazy() || $generator_type->subtype['value']->isLazy())) {
            throw new ScopeError(Localization::message('TYP280', [$generator_type]));
        }

        if (null !== $this->key) {
            $this->scope->setMeta(
                Meta::M_TYPE,
                $this->key,
                $generator_type->isList()
                    ? new Type(NativeQuackType::T_NUMBER)
                    : clone $generator_type->subtype['key']
            );
        }

        $this->scope->setMeta(Meta::M_TYPE, $this->alias, $generator_type->isList()
            ? clone $generator_type->subtype
            : clone $generator_type->subtype['value']);

        foreach ($this->body as $stmt) {
            $stmt->runTypeChecker();
        }
    }
}
