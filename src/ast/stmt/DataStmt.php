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

use \QuackCompiler\Ast\Types\NameType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Types\TaggedUnion;
use \QuackCompiler\Types\TypeError;

class DataStmt extends Stmt
{
    public $name;
    public $parameters;
    public $values;

    public function __construct($name, $parameters, $values)
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->values = $values;
    }

    public function format(Parser $parser)
    {
        $source = 'data ';
        $source .= $this->name;

        if (sizeof($this->parameters) > 0) {
            $source .= '(';
            $source .= implode(', ', $this->parameters);
            $source .= ')';
        }

        $source .= ' :- ';
        $source .= implode(' or ', array_map(function ($value) use ($parser) {
            return $value->format($parser);
        }, $this->values));

        $source .= PHP_EOL;
        return $source;
    }

    public function injectScope($parent_scope)
    {
        // Bind input parameters
        $this->scope = new Scope($parent_scope);
        foreach ($this->parameters as $parameter) {
            $this->scope->insert($parameter, Symbol::S_TYPE | Symbol::S_DATA_PARAM);
            $this->scope->setMeta(Meta::M_TYPE, $parameter, new NameType($parameter, []));
            // Initialize parameter with no constraints
            $this->scope->setMeta(Meta::M_KIND_CONSTRAINTS, $parameter, []);
        }

        // Store union declaration in the current scope
        // TODO: Use secondary scope for union declaration
        $parent_scope->insert($this->name, Symbol::S_TYPE | Symbol::S_DATA);

        $declared = [];
        foreach ($this->values as $value) {
            if (isset($declared[$value->name])) {
                throw new TypeError(Localization::message('SCO030', [$value->name, $this->name]));
            }

            $value->bindDataStmt($this);
            $value->injectScope($parent_scope, $this->scope);
            $declared[$value->name] = true;
        }
    }

    public function runTypeChecker()
    {
        // Check constraints that came from bindScope for parameter kinds
        foreach ($this->parameters as $parameter) {
            $constraints = $this->scope->getMeta(Meta::M_KIND_CONSTRAINTS, $parameter);
            $arity = null;
            foreach ($constraints as $constraint) {
                if (null === $arity) {
                    $arity = $constraint['size'];
                    continue;
                }

                if ($constraint['size'] !== $arity) {
                    throw new TypeError(
                        Localization::message('TYP470', [$parameter, $this->name, $arity, $constraint['size']])
                    );
                }
            }
        }
    }
}
