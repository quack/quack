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
namespace QuackCompiler\TypeChecker;

use \QuackCompiler\Ast\Types\FunctionType;
use \QuackCompiler\Ast\Types\TypeNode;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Types\TypeError;

trait FunctionTypeChecker
{
    public function check(TypeNode $other)
    {
        $message = Localization::message('TYP350', [$this, $other]);
        if (!($other instanceof FunctionType)) {
            return false;
        }

        // Functions with different number of arguments
        $self_arity = count($this->parameters);
        $other_arity = count($other->parameters);
        if ($self_arity !== $other_arity) {
            $message .= '     > ' . Localization::message('TYP360', [$self_arity, $other_arity]);
            throw new TypeError($message);
        }

        // Check type for each parameter
        for ($i = 0; $i < $self_arity; $i++) {
            $self_type = $this->parameters[$i];
            $other_type = $other->parameters[$i];

            if (!$self_type->check($other_type)) {
                $message .= '     > ' . Localization::message('TYP370', [$i + 1, $self_type, $other_type]);
                throw new TypeError($message);
            }
        }

        // Check return type
        if (!$this->return->check($other->return)) {
            $message .= '     > ' . Localization::message('TYP380', [$this->return, $other->return]);
            throw new TypeError($message);
        }

        return true;
    }
}
