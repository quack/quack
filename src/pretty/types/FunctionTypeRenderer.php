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
namespace QuackCompiler\Pretty\Types;

use \QuackCompiler\Pretty\Colorizer;

trait FunctionTypeRenderer
{
    private function fromUnicode($char)
    {
        return json_decode('"' . $char . '"');
    }

    public function render(Colorizer $renderer)
    {
        $result = '';
        if (count($this->generics) > 0) {
            $result .= $renderer->bold($this->fromUnicode('\u2200')) . ' ';
            $result .= implode(', ', array_map([$renderer, 'red'], $this->generics));
            $result .= ' ';
        }

        $result .= $renderer->magenta('&[');
        $result .= implode(', ', array_map(function ($parameter) use ($renderer) {
            return $parameter->render($renderer);
        }, $this->parameters));
        $result .= $renderer->magenta(']');
        $result .= ': ';
        $result .= $this->return->render($renderer);
        return $result;
    }
}

