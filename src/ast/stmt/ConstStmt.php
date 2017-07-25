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

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Types\TypeError;

class ConstStmt extends Stmt
{
    public $name;
    public $type;
    public $value;
    private $scoperef;

    public function __construct($name, $type, $value)
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
    }

    public function format(Parser $parser)
    {
        $source = 'const ' . $this->name;

        if (!is_null($this->type)) {
            $source .= ' :: ' . $this->type;
        }

        $source .= ' :- ' . $this->value->format($parser);
        $source .= PHP_EOL;
        return $source;
    }

    public function injectScope(&$parent_scope)
    {
        $this->scoperef = $parent_scope;
        $this->value->injectScope($parent_scope);
    }

    public function runTypeChecker()
    {
        $value_type = $this->value->getType();
        $type = null;
        if (is_null($this->type)) {
            $type = $value_type;
        } else {
            if (!$this->type->check($value_type)) {
                throw new TypeError(Localization::message('TYP300', [$this->name, $this->type, $value_type]));
            }
            $type = $this->type;
        }

        $this->scoperef->setMeta(Meta::M_TYPE, $this->name, $type);
    }
}
