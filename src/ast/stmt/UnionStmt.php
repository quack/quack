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

use \QuackCompiler\Ast\Types\NameType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Types\TaggedUnion;
use \QuackCompiler\Types\TypeError;

class UnionStmt
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
        $source = 'union ';
        $source .= $this->name;

        if (sizeof($this->parameters) > 0) {
            $source .= '(';
            $source .= implode(', ', $this->parameters);
            $source .= ')';
        }

        $source .= ' :- ';
        $source .= implode(' or ', array_map(function ($value) {
            list ($name, $types) = $value;
            $source = $name;

            if (sizeof($types) > 0) {
                $source .= '(';
                $source .= implode(', ', $types);
                $source .= ')';
            }
            return $source;
        }, $this->values));

        $source .= PHP_EOL;
        return $source;
    }

    public function injectScope($parent_scope)
    {
        // Bind input parameters
        $this->scope = new Scope($parent_scope);
        foreach ($this->parameters as $parameter) {
            $this->scope->insert($parameter, Kind::K_TYPE | Kind::K_UNION_PARAM);
            $this->scope->setMeta(Meta::M_TYPE, $parameter, new NameType($parameter, []));
        }

        $declared = [];
        // Declare union type
        $tagged_union = new TaggedUnion($this->name, $this->parameters, $this->values);
        $parent_scope->insert($this->name, Kind::K_TYPE | Kind::K_UNION);
        $parent_scope->setMeta(Meta::M_CONS, $this->name, $this->values);
        $parent_scope->setMeta(Meta::M_TYPE, $this->name, $tagged_union);

        foreach ($this->values as $value) {
            list ($name, $types) = $value;
            if (isset($declared[$name])) {
                throw new TypeError(Localization::message('SCO030', [$name, $this->name]));
            }

            $declared[$name] = true;
            $parent_scope->insert($name, Kind::K_TYPE | Kind::K_UNION_MEMBER);
            $parent_scope->setMeta(Meta::M_TYPE, $name, $tagged_union);
            $parent_scope->setMeta(Meta::M_CONS, $name, $types);
        }

        $tagged_union->bindScope($this->scope);
    }

    public function runTypeChecker()
    {
        // TODO: Run typechecker for subnodes
    }
}
