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
namespace QuackCompiler\Intl;

class Localization
{
    const LOCALE = 'en-US';
    static $messages = null;

    public static function message($key, $arguments)
    {
        if (null === static::$messages) {
            static::$messages = static::readJSON();
        }

        return call_user_func_array('sprintf',
            array_merge([static::$messages[$key]], $arguments));
    }

    private static function readJSON()
    {
        $file = realpath(dirname(__FILE__) . '/locales/' . static::LOCALE . '.json');
        return json_decode(file_get_contents($file), true);
    }
}
