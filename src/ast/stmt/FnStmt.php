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

use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\ScopeError;

class FnStmt extends Stmt
{
    public $signature;
    public $body;
    public $is_pub;
    public $is_method;
    public $is_short;

    private $flag_bind_self = false;
    private $flag_bind_super = false;

    public function __construct($signature, $body, $is_pub, $is_method, $is_short)
    {
        $this->signature = $signature;
        $this->body = $body;
        $this->is_pub = $is_pub;
        $this->is_method = $is_method;
        $this->is_short = $is_short;
        // Standard compatibilization for `Named'
        $this->name = $this->signature->name;
    }

    public function format(Parser $parser)
    {
        $source = '';

        if (!$this->is_method) {
            $source = $this->is_pub ? 'pub fn ' : 'fn ';
        }

        $source .= ($this->signature->is_recursive ? 'rec ' : '')
                 . ($this->signature->is_reference ? '*' : '')
                 . $this->signature->name;
        $source .= '(';
        $source .= implode(', ', array_map(function ($param) {
            return ($param->is_reference ? '*' : '') . $param->name;
        }, $this->signature->parameters));
        $source .= ')';

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
        $this->createScopeWithParent($parent_scope);

        // Pre-inject `self' if it should
        if ($this->flag_bind_self) {
            $this->scope->insert('self', Kind::K_VARIABLE | Kind::K_INITIALIZED | Kind::K_SPECIAL);
        }

        // When we are in the blueprint context and it extends another
        // blueprint, insert `super'
        if ($this->flag_bind_super) {
            $this->scope->insert('super', Kind::K_VARIABLE | Kind::K_INITIALIZED | Kind::K_SPECIAL);
        }

        // Pre-inject parameters
        foreach ($this->signature->parameters as $param) {
            if ($this->scope->hasLocal($param->name)) {
                throw new ScopeError([
                    'message' => "Duplicated parameter `{$param->name}' in function {$this->signature->name}"
                ]);
            }

            $this->scope->insert($param->name, Kind::K_INITIALIZED | Kind::K_MUTABLE | Kind::K_VARIABLE | Kind::K_PARAMETER);
        }

        if ($this->is_short) {
            $this->body->injectScope($this->scope);
        } else {
            $this->bindDeclarations($this->body);
            foreach ($this->body as $node) {
                $node->injectScope($this->scope);
            }
        }
    }

    public function flagBindSelf()
    {
        $this->flag_bind_self = true;
    }

    public function flagBindSuper()
    {
        $this->flag_bind_super = true;
    }

    public function runTypeChecker()
    {
        // TODO
    }
}
