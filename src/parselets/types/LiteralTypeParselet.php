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
namespace QuackCompiler\Parselets\Types;

use \QuackCompiler\Ast\Types\LiteralType;
use \QuackCompiler\Ast\Types\GenericType;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parselets\PrefixParselet;
use \QuackCompiler\Types\NativeQuackType;

class LiteralTypeParselet implements PrefixParselet
{
    public function parse($grammar, Token $token)
    {
        $names = [
            'string'  => NativeQuackType::T_STR,
            'number'  => NativeQuackType::T_NUMBER,
            'boolean' => NativeQuackType::T_BOOL,
            'regex'   => NativeQuackType::T_REGEX,
            'block'   => NativeQuackType::T_BLOCK,
            'byte'    => NativeQuackType::T_BYTE,
            'atom'    => NativeQuackType::T_ATOM
        ];
        $name = $token->getContent();

        return array_key_exists($name, $names)
            ? new LiteralType($names[$name])
            : new GenericType($name);
    }
}
