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
namespace QuackCompiler\Ast;

use \QuackCompiler\Ast\Stmt\BlockStmt;
use \QuackCompiler\Parser\Parser;

class Util
{
    public static function makeBlock($body, Parser $parser)
    {
        $string_builder = [];

        if ($body instanceof BlockStmt) {
            $string_builder[] = $body->format($parser);
        } else {
            $string_builder[] = '[';
            $string_builder[] = PHP_EOL;
            $string_builder[] = '  ';
            $string_builder[] = $body->format($parser);
            $string_builder[] = ']';
        }

        return implode($string_builder);
    }
}
