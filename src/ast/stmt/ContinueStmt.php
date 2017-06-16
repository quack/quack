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
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\ScopeError;

class ContinueStmt extends Stmt
{
    public $label;

    public function __construct($label = null)
    {
        $this->label = $label;
    }

    public function format(Parser $parser)
    {
        $source = 'continue';

        if (null !== $this->label) {
            $source .= ' ';
            $source .= $this->label;
        }

        $source .= PHP_EOL;
        return $source;
    }

    public function injectScope(&$parent_scope)
    {
        // Assert that we are receiving a declared label
        $label = $parent_scope->lookup($this->label);

        if (null === $label) {
            // When the label doesn't exist
            throw new ScopeError([
                'message' => "Called `continue' with undeclared label `{$this->label}'"
            ]);
        } elseif (!($label & Kind::K_LABEL)) {
            // When the symbol exist, but it's not a label
            throw new ScopeError([
                'message' => "Called `continue' with invalid label `{$this->label}'"
            ]);
        }

        // Usages of the label
        $refcount = &$parent_scope->getMeta(Meta::M_REF_COUNT, $this->label);
        if (null === $refcount) {
            $parent_scope->setMeta(Meta::M_REF_COUNT, $this->label, 1);
        } else {
            $parent_scope->setMeta(Meta::M_REF_COUNT, $this->label, $refcount + 1);
        }
    }

    public function runTypeChecker()
    {
        // Pass
    }
}
