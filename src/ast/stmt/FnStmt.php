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

use \QuackCompiler\Ast\Types\FunctionType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\TypeError;

class FnStmt extends Stmt
{
    public $signature;
    public $body;
    public $is_short;

    public function __construct(FnSignatureStmt $signature, $body, $is_short)
    {
        $this->signature = $signature;
        $this->body = $body;
        $this->is_short = $is_short;
        // Standard compatibilization for `Named'
        $this->name = $this->signature->name;
    }

    public function format(Parser $parser)
    {
        $source = 'fn ';
        $source .= $this->signature->format($parser);

        if ($this->is_short) {
            $source .= ' :- ';
            $source .= $this->body->format($parser);
        } else {
            $source .= PHP_EOL;
            $source .= $this->body->format($parser);
            $source .= $parser->indent();
            $source .= 'end';
        }

        $source .= PHP_EOL;

        return $source;
    }

    public function injectScope($parent_scope)
    {
        $parent_scope->insert($this->signature->name, Symbol::S_VARIABLE);
        $this->scope = new Scope($parent_scope);

        // Pre-inject parameters
        $this->signature->injectScope($this->scope);
        $this->body->injectScope($this->scope);
    }

    public function runTypeChecker()
    {
        // TODO: Need to implement proofs for blocks
        // TODO: don't default to is_short
        $parameters_types = $this->signature->getParametersTypes();

        // If the function type is statically defined, preset it
        if (null !== $this->signature->type) {
            $function_type = new FunctionType($parameters_types, $this->signature->type);
            $this->scope->setMeta(Meta::M_TYPE, $this->signature->name, $function_type);
            $this->injectParametersTypes($parameters_types);

            // Compare return type with body type
            $body_type = $this->body->getType();
            if (!$this->signature->type->check($body_type)) {
                throw new TypeError(Localization::message('TYP380', [$this->signature->type, $body_type]));
            }
        } else {
            // Bind returned type to the function
            $this->injectParametersTypes($parameters_types);
            $body_type = $this->body->getType();
            $function_type = new FunctionType($parameters_types, $body_type);
            $this->scope->setMeta(Meta::M_TYPE, $this->signature->name, $function_type);
        }
    }

    private function injectParametersTypes($parameters_types)
    {
        $size = count($parameters_types);
        for ($i = 0; $i < $size; $i++) {
            $parameter = $this->signature->parameters[$i]->name;
            $type = $parameters_types[$i];
            $this->scope->setMeta(Meta::M_TYPE, $parameter, $type);
        }
    }
}
