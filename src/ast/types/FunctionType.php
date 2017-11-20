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
namespace QuackCompiler\Ast\Types;

use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Pretty\Types\FunctionTypeRenderer;
use \QuackCompiler\Scope\Scope;
use \QuackCompiler\TypeChecker\FunctionTypeChecker;
use \QuackCompiler\Types\TypeError;

class FunctionType extends TypeNode
{
    use FunctionTypeChecker;
    use FunctionTypeRenderer;

    public $parameters;
    public $return;
    public $generics;

    public function __construct($parameters, $return, $generics = [])
    {
        $this->parameters = $parameters;
        $this->return = $return;
        $this->generics = $generics;
    }

    private function fromUnicode($char)
    {
        return json_decode('"' . $char . '"');
    }

    public function __toString()
    {
        $source = '';
        if (count($this->generics) > 0) {
            $source .= $this->fromUnicode('\u2200') . ' ' . implode (', ', $this->generics ) . ' ';
        }
        $source .= '&[' . join(', ', $this->parameters) . ']: ' . $this->return;

        return $this->parenthesize($source);
    }

    public function getKind()
    {
        return implode(' -> ', array_fill(0, count($this->parameters) + 1, '*'));
    }

    public function computeCall($arguments)
    {
        $received_arguments_count = count($arguments);
        for ($index = 0; $index < $received_arguments_count; $index++) {
            $received = $arguments[$index]->getType();
            $expected = $this->parameters[$index];

            $context = [];
            $expected->unify($received, $context);
        }
    }
}
