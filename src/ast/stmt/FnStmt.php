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
    public $name;
    public $by_reference;
    public $body;
    public $parameters;
    public $is_bang;
    public $is_pub;
    public $is_rec;
    public $is_method;

    private $flag_bind_self = false;
    private $flag_bind_super = false;

    public function __construct($name, $by_reference, $body, $parameters, $is_bang, $is_pub, $is_rec, $is_method)
    {
        $this->name = $name;
        $this->by_reference = $by_reference;
        $this->body = $body;
        $this->parameters = $parameters;
        $this->is_bang = $is_bang;
        $this->is_pub = $is_pub;
        $this->is_rec = $is_rec;
        $this->is_method = $is_method;
    }

    public function format(Parser $parser)
    {
        $source = '';

        if (!$this->is_method) {
            $source = $this->is_pub
                ? 'pub fn '
                : 'fn ';
        }

        if ($this->by_reference) {
            $source .= '* ';
        }

        if ($this->is_rec) {
            $source .= 'rec ';
        }

        $source .= $this->name;

        if (sizeof($this->parameters) > 0) {
            $source .= '( ';

            $source .= implode('; ', array_map(function ($param) {
                $subsource = '';
                $obj = (object) $param;

                if ($obj->ellipsis) {
                    $subsource .= '... ';
                }

                if ($obj->by_reference) {
                    $subsource .= '*';
                }

                $subsource .= $obj->name;

                return $subsource;
            }, $this->parameters));

            $source .= ' )';
        } else {
            $source .= $this->is_bang
                ? '!'
                : '()';
        }

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
        foreach (array_map(function ($item) {
            return (object) $item;
        }, $this->parameters) as $param) {
            if ($this->scope->hasLocal($param->name)) {
                throw new ScopeError([
                    'message' => "Duplicated parameter `{$param->name}' in function {$this->name}"
                ]);
            }

            $this->scope->insert($param->name, Kind::K_INITIALIZED | Kind::K_MUTABLE | Kind::K_VARIABLE | Kind::K_PARAMETER);
        }

        if (null !== $this->body) {
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
}
