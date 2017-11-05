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

use \QuackCompiler\Ast\Types\TypeNode;
use \QuackCompiler\Intl\Localization;
use \QuackCompiler\Scope\Meta;
use \QuackCompiler\Types\TypeError;

trait NameTypeChecker
{
    public function check(TypeNode $other)
    {
        $type = $this->scope->getMeta(Meta::M_TYPE, $this->name);
        return $type->check($other);
    }

    public function unify(TypeNode $other, &$context)
    {
        $my_kind = $this->getKind();
        $your_kind = $other->getKind();

        if ($my_kind !== $your_kind) {
            throw new TypeError(Localization::message('TYP480', [
                $this, $other, $my_kind, $your_kind
            ]));
        }

        if ($this->is_generic) {
            $context[$this->name] = $other;
        }

        for ($index = 0; $index < count($this->values); $index++) {
            $my_baby = $this->values[$index];
            $your_baby = $other->parameters[$index];
            $my_baby->unify($your_baby, $context);
        }
    }
}
