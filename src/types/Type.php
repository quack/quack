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
    public $props; // For objects and multiple subtyping access

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function __toString()
    {
        if (null === $this->code) {
            return 'unknown';
        }

        switch ($this->code) {
            case NativeQuackType::T_STR:
                return 'string';
            case NativeQuackType::T_INT:
                return 'integer';
            case NativeQuackType::T_DOUBLE:
                return 'double';
            case NativeQuackType::T_BOOL:
                return 'boolean';
            case NativeQuackType::T_ATOM:
                return 'atom';
            case NativeQuackType::T_REGEX:
                return 'regex';
            case NativeQuackType::T_LIST:
                return "list.of({$this->subtype})";
            case NativeQuackType::T_LAZY:
                return '?';
            case NativeQuackType::T_BLOCK:
                return 'block';
            case NativeQuackType::T_OBJ:
                // TODO: Implement indent-level for types
                $space = sizeof($this->props) > 0 ? " " : "";
                $src = "object.of(?).with {{$space}";
                foreach ($this->props as $key => $value) {
                    $src .= "{$key} :: {$value}";
                    // When is not the last one, append comma
                    if ($key !== key(array_slice($this->props, -1, 1, true))) {
                        $src .= ', ';
                    }
                }
                $src .= "{$space}}";
                return $src;
            case NativeQuackType::T_MAP:
                return "map.of({$this->props['key']} -> {$this->props['value']})";
            default:
                return 'unknown';
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

    public function isBlock()
    {
        return NativeQuackType::T_BLOCK === $this->code;
    }

    public function hasSubtype()
    {
        return null !== $this->subtype || (null !== $this->props && sizeof($props) > 0);
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
            if ($this->code !== $other->code) {
                return false;
            }

            switch ($this->code) {
                case NativeQuackType::T_LIST:
                    return $this->subtype->isCompatibleWith($other->subtype);
                case NativeQuackType::T_MAP:
                    return $this->props['key']->isCompatibleWith($other->props['key'])
                        && $this->props['value']->isCompatibleWith($other->props['value']);
                default:
                    return false; // NonImplemented
            }

            return $this->code === $other->code
                && $this->subtype->isCompatibleWith($other->subtype);
        }

        return $this->code === $other->code;
    }

    public function getDeepestSubtype()
    {
        // subtype :: (MonadType a, b) => a -> b
        // subtype (Literal a) = a
        // subtype (Subtyped a) = subtype a
        // subtype (Proped (Type a) (Type b)) = Type { props = map subtype [a, b] } -- <*>
        if (!$this->hasSubtype()) {
            return $this;
        }

        switch ($this->code) {
            case NativeQuackType::T_LIST:
                return $this->subtype->getDeepestSubtype();
            default: // ?
                return $this;
        }
    }

    public function importFrom(Type $type)
    {
        $this->code = $type->code;
        $this->subtype = $type->subtype;
        $this->supertype = $type->supertype;
        $this->props = $type->props;
    }

    public static function getBaseType($types)
    {
        if (0 === sizeof($types)) {
            return null;
        }

        // Currently, implemented only for numbers
        if ($types[0]->isNumber()) {
            return new Type(max(array_map(
                function ($type) { return $type->code; },
                $types
            )));
        }

        // No base type. Let's clone the initial type
        return clone $types[0];
    }
}
