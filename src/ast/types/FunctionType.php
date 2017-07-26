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
namespace QuackCompiler\Ast\Types;

class FunctionType extends TypeNode
{
    public $parameters;
    public $return;

    public function __construct($parameters, $return)
    {
        $this->parameters = $parameters;
        $this->return = $return;
    }

    public function __toString()
    {
        return $this->parenthesize(
            '&[' . join(', ', $this->parameters) . '] -> ' . $this->return
        );
    }

    public function check(TypeNode $other)
    {
        if (!($other instanceof FunctionType)) {
            return false;
        }

        // Functions with different number of arguments
        $self_arity = sizeof($this->parameters);
        $other_arity = sizeof($other->parameters);
        if ($self_arity !== $other_arity) {
            return false;
        }

        // Check type for each parameter
        for ($i = 0; $i < $self_arity; $i++) {
            $self_type = $this->parameters[$i];
            $other_type = $other->parameters[$i];

            if (!$self_type->check($other_type)) {
                return false;
            }
        }

        // Check return type
        if (!$this->return->check($other->return)) {
            return false;
        }

        return true;
    }
}
