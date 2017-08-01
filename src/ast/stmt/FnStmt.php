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

use \QuackCompiler\Ast\Types\FunctionType;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\TypeError;

class FnStmt extends Stmt
{
    public $signature;
    public $body;
    public $is_method;
    public $is_short;

    private $flag_bind_self = false;

    public function __construct(FnSignatureStmt $signature, $body, $is_method, $is_short)
    {
        $this->signature = $signature;
        $this->body = $body;
        $this->is_method = $is_method;
        $this->is_short = $is_short;
        // Standard compatibilization for `Named'
        $this->name = $this->signature->name;
    }

    public function format(Parser $parser)
    {
        $source = $this->is_method ? '' : 'fn ';
        $source .= $this->signature->format($parser);

        if ($this->is_short) {
            $source .= ' :- ';
            $source .= $this->body->format($parser);
        } else {
            $source .= PHP_EOL;

            $parser->openScope();

            foreach ($this->body as $stmt) {
                $source .= $parser->indent();
                $source .= $stmt->format($parser);
            }

            $parser->closeScope();

            $source .= $parser->indent();
            $source .= 'end';
        }

        $source .= PHP_EOL;

        return $source;
    }

    public function injectScope(&$parent_scope)
    {
        $parent_scope->insert($this->signature->name, Kind::K_VARIABLE | Kind::K_FUNCTION);
        $this->scope = new Scope($parent_scope);

        // Pre-inject `self' if it should
        if ($this->flag_bind_self) {
            $this->scope->insert('self', Kind::K_VARIABLE | Kind::K_INITIALIZED | Kind::K_SPECIAL);
        }

        // Pre-inject parameters
        $this->signature->injectScope($this->scope);

        if ($this->is_short) {
            $this->body->injectScope($this->scope);
        } else {
            foreach ($this->body as $node) {
                $node->injectScope($this->scope);
            }
        }
    }

    public function flagBindSelf()
    {
        $this->flag_bind_self = true;
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
        $size = sizeof($parameters_types);
        for ($i = 0 ; $i < $size; $i++) {
            $parameter = $this->signature->parameters[$i]->name;
            $type = $parameters_types[$i];
            $this->scope->setMeta(Meta::M_TYPE, $parameter, $type);
        }
    }
}
