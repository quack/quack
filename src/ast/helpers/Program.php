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
namespace QuackCompiler\Ast\Helpers;

use \Exception;
use \QuackCompiler\Ds\Set;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Scope;

class Program
{
    public $stmt_list;
    private $non_generic;

    public function __construct($stmt_list)
    {
        $this->non_generic = new Set();
        $this->stmt_list = $stmt_list;
    }

    public function format(Parser $parser)
    {
        $source = '';

        foreach ($this->stmt_list as $stmt) {
            $source .= $stmt->format($parser);
        }

        return $source;
    }

    public function injectScope($parent_scope)
    {
        $this->scope = $parent_scope;

        foreach ($this->stmt_list as $node) {
            $node->injectScope($this->scope);
        }
    }

    public function runTypeChecker(Scope $scope)
    {
        $non_generic = new Set();
        foreach ($this->stmt_list as $node) {
            $node->runTypeChecker($scope, $this->non_generic);
        }
    }

    public function attachValidAST($ast)
    {
        $safe_scope = clone $this->scope;
        $safe_non_generic = clone $this->non_generic;

        try {
            foreach ($ast->stmt_list as $node) {
                $node->injectScope($this->scope);
            }

            foreach ($ast->stmt_list as $node) {
                $node->runTypeChecker($safe_scope, $safe_non_generic);
            }

            $this->stmt_list = array_merge($this->stmt_list, $ast->stmt_list);
        } catch (Exception $e) {
            $this->scope = $safe_scope;
            throw $e;
        }
    }
}
