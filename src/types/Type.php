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
namespace QuackCompiler\Types;

class Type
{
    public $code;
    public $subtype;
    public $supertype;
    public $isref;

    public function __construct($code)
    {
        $this->code = $code;
        $this->isref = false;
    }

    public function __toString()
    {
        if (null === $this->code) {
            return 'unknown';
        }

        $self = $this;
        $transform = function ($text) use ($self) {
            return $self->isref ? "*{$text}" : $text;
        };

        switch ($this->code) {
            case NativeQuackType::T_STR:
                return $transform('string');
            case NativeQuackType::T_INT:
                return $transform('integer');
            case NativeQuackType::T_DOUBLE:
                return $transform('double');
            case NativeQuackType::T_BOOL:
                return $transform('boolean');
            case NativeQuackType::T_ATOM:
                return $transform('atom');
            case NativeQuackType::T_REGEX:
                return $transform('regex');
            case NativeQuackType::T_LIST:
                return $transform("list.of({$this->subtype})");
            case NativeQuackType::T_LAZY:
                return $transform('?');
            default:
                return transform('unknown');
        }
    }

    public function isString()
    {
        return NativeQuackType::T_STR === $this->code;
    }

    public function isInteger()
    {
        return NativeQuackType::T_INT === $this->code;
    }

    public function isDouble()
    {
        return NativeQuackType::T_DOUBLE === $this->code;
    }

    public function isBoolean()
    {
        return NativeQuackType::T_BOOL === $this->code;
    }

    public function isAtom()
    {
        return NativeQuackType::T_ATOM === $this->code;
    }

    public function isRegex()
    {
        return NativeQuackType::T_REGEX === $this->code;
    }

    public function isList()
    {
        return NativeQuackType::T_LIST === $this->code;
    }

    public function isNumber()
    {
        return $this->isInteger() || $this->isDouble();
    }

    public function isLazy()
    {
        return NativeQuackType::T_LAZY === $this->code;
    }

    public function hasSubtype()
    {
        return null !== $this->subtype;
    }

    public function hasSuperType()
    {
        return null !== $this->supertype;
    }

    public function isCompatibleWith(Type $other)
    {
        if ($this->isNumber() && $other->isNumber()) {
            return true;
        }

        if ($this->hasSubtype() && $other->hasSubtype()) {
            return $this->code === $other->code
                && $this->subtype->isCompatibleWith($other->subtype);
        }

        return $this->code === $other->code;
    }

    public function getDeepestSubtype()
    {
        // subtype (Literal a) = a
        // subtype (Subtyped a) = subtype a
        return !$this->hasSubtype()
            ? $this
            : $this->subtype->getDeepestSubtype();
    }
}
