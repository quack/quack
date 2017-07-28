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
namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parselets\Expr\LambdaParselet;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Kind;
use \QuackCompiler\Scope\ScopeError;

class LambdaExpr extends Expr
{
    public $parameters;
    public $kind;
    public $body;
    public $has_brackets;

    public function __construct($parameters, $kind, $body, $has_brackets)
    {
        $this->parameters = $parameters;
        $this->kind = $kind;
        $this->body = $body;
        $this->has_brackets = $has_brackets;
    }

    public function format(Parser $parser)
    {
        $source = '&';

        switch (sizeof($this->parameters)) {
            case 0:
                $source .= '[]';
                break;
            case 1:
                if ($this->has_brackets) {
                    $source .= '[' . $this->parameters[0]->name . ']';
                } else {
                    $source .= $this->parameters[0]->name;
                }
                break;
            default:
                $source .= '[';
                $source .= implode(', ', array_map(function($param) {
                    $parameter = $param->name;

                    if (null !== $param->type) {
                        $parameter .= " :: {$param->type}";
                    }

                    return $parameter;
                }, $this->parameters));
                $source .= ']';
        }

        $source .= ' -> ';

        if (LambdaParselet::TYPE_EXPRESSION === $this->kind) {
            $source .= $this->body->format($parser);
        } else {
            $source .= 'begin' . PHP_EOL;

            $parser->openScope();

            foreach ($this->body as $stmt) {
                $source .= $parser->indent();
                $source .= $stmt->format($parser);
            }

            $parser->closeScope();
            $source .= $parser->indent();
            $source .= 'end';
            $source .= PHP_EOL;
        }

        return $this->parenthesize($source);
    }

    public function injectScope(&$parent_scope)
    {
        $this->createScopeWithParent($parent_scope);

        foreach ($this->parameters as $param) {
            if ($this->scope->hasLocal($param->name)) {
                throw new ScopeError(Localization::message('SCO010', [$param->name]));
            }

            $this->scope->insert($param->name, Kind::K_INITIALIZED | Kind::K_VARIABLE | Kind::K_PARAMETER | Kind::K_MUTABLE);
        }

        if (LambdaParselet::TYPE_STATEMENT === $this->kind) {
            $this->bindDeclarations($this->body);

            foreach ($this->body as $node) {
                $node->injectScope($this->scope);
            }
        } else {
            $this->body->injectScope($this->scope);
        }
    }
}
