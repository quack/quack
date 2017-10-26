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

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Symbol;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Scope\ScopeError;

class BreakStmt extends Stmt
{
    public $label;
    public $is_explicit;

    public function __construct($label = null)
    {
        $this->label = $label;
        $this->is_explicit = null !== $label;
    }

    public function format(Parser $parser)
    {
        $source = 'break';

        if ($this->is_explicit) {
            $source .= ' ';
            $source .= $this->label;
        }

        $source .= PHP_EOL;
        return $source;
    }

    public function injectScope($parent_scope)
    {
        if (!$this->is_explicit) {
            // Check if there is an implicit label
            $label = $parent_scope->getMetaInContext(Meta::M_LABEL);

            // If there is no implicit labels in the context, then the user is
            // calling 'break' outsite a loop.
            if (null === $label) {
                throw new ScopeError(Localization::message('SCO140', ['break']));
            }
        } else {
            $meta_label = $parent_scope->getMetaInContext(Meta::M_LABEL);

            // If meta_label is null, the user is calling 'break' outside a loop
            if (null === $meta_label) {
                throw new ScopeError(Localization::message('SCO140', ['break']));
            }

            $label = $parent_scope->lookup($this->label);

            // When the symbol doesn't exist
            if (null === $label) {
                throw new ScopeError(Localization::message('SCO150', ['break', $this->label]));
            }

            // When the symbol exist, but it's not a label
            if (~$label & Symbol::S_LABEL) {
                throw new ScopeError(Localization::message('SCO160', ['break', $this->label]));
            }

            $refcount = $parent_scope->getMeta(Meta::M_REF_COUNT, $this->label);
            if (null === $refcount) {
                $parent_scope->setMeta(Meta::M_REF_COUNT, $this->label, 1);
            } else {
                $parent_scope->setMeta(Meta::M_REF_COUNT, $this->label, $refcount + 1);
            }
        }
    }

    public function runTypeChecker()
    {
        // Pass
    }
}
