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
namespace QuackCompiler\Intl;

class Localization
{
    private static $messages = null;

    public static function message($key, $arguments)
    {
        if (null === static::$messages) {
            static::$messages = static::readJSON();
        }

        return sprintf(...array_merge([static::$messages[$key]], $arguments));
    }

    private static function readJSON()
    {
        list ($exists_locale, $file) = static::getFile(getenv('LANGUAGE'));
        $locale = $exists_locale ? $file : static::getFile('en_US')[1];
        return json_decode(file_get_contents($locale), true);
    }

    private static function getFile($locale)
    {
        $path = realpath(dirname(__FILE__) . '/locales/' . $locale . '.json');
        return [file_exists($path), $path];
    }
}
