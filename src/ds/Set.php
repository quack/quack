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
namespace QuackCompiler\Ds;

use \Countable;
use \Iterator;

class Set implements Countable, Iterator
{
    private $position;
    private $keys = [];

    public function __construct()
    {
        $this->position = 0;
    }

    public function has($key)
    {
        foreach ($this->keys as $original_key) {
            if ($original_key === $key) {
                return true;
            }
        }

        return false;
    }

    public function push($key)
    {
        if (!$this->has($key)) {
            $this->keys[] = $key;
        }
    }

    public function count()
    {
        return count($this->keys);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->keys[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->keys[$this->position]);
    }
}
