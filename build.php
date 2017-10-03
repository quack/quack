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

# This file is responsible for bundling Quack compiler source files
# together in order to generate one single and small file with all
# Quack compiler stuff. I'm sorry, this was written fastly, so the code
# may not be the best. Take care, here be dragons!

/**
 * Receives a source string and returns it minified. Converts
 * namespaces to bundle-compatible ones, strips comments and
 * converts whitespaces.
 *
 * @param string $source
 * @return string
 */
function minify($source)
{
    $index = 0;
    $tokens = token_get_all($source);
    $length = sizeof($tokens);
    $result = '';
    $has_namespace = false;

    while ($index < $length) {
        $token = $tokens[$index++];

        if (is_string($token)) {
            $result .= $token;
        }

        list ($tag, $value) = $token;
        // Strip comments and PHP tags
        if (in_array($tag, [T_COMMENT, T_DOC_COMMENT, T_OPEN_TAG, T_CLOSE_TAG], true)) {
            continue;
        }

        // Convert multiple whitespaces and newlines to 1 space
        if (T_WHITESPACE === $tag) {
            $result .= ' ';
            continue;
        }

        // Transform namespace *; into namespace {
        if (T_NAMESPACE === $tag) {
            $result .= $value;
            $has_namespace = true;
            while (true) {
                $token = $tokens[$index++];

                if (';' === $token) {
                    $result .= ' {';
                    break;
                }

                $result .= is_string($token) ? $token : $token[1];
            }
            continue;
        }

        $result .= $value;
    }

    if ($has_namespace) {
        $result .= '}';
    }

    return trim($result);
}

/**
 * Bundles PHP sources to one single file.
 *
 * @param array $config
 * @return void
 */
function bundle($config) {
    // Configuration
    $bundle = $config['bundle'];
    $resources = $config['resources'];

    $contents = [];
    foreach ($resources as $resource) {
        foreach ($resource->readFiles() as $source) {
            $contents[] = minify($source) . PHP_EOL;
        }
    }

    echo implode('', $contents);
}

// Modeling resource types
abstract class Resource
{
    protected $path;

    public function __construct($path)
    {
        $this->path = realpath($path);
    }

    abstract public function readFiles();
}

class ResourceFile extends Resource
{
    public function readFiles()
    {
        return [file_get_contents($this->path)];
    }
}

class ResourceBuffer extends Resource
{
    private $buffer;

    public function __construct($buffer)
    {
        $this->buffer = $buffer;
    }

    public function readFiles()
    {
        return [$this->buffer];
    }
}

/**
 * Returns the bundle for inline localization
 */
function getLocalesBuffer()
{
    $strings = json_decode(file_get_contents('src/intl/locales/en-US.json'));
    $messages = [];

    foreach ($strings as $key => $value) {
        $messages[] = '\'' . $key . '\' => \'' . addslashes($value) . '\'';
    }

    return '
        <?php
        namespace QuackCompiler\Intl;
        class Localization
        {
            public static function message($key, $arguments)
            {
                if (null === static::$messages) {
                    static::$messages = [' . implode(', ', $messages) . '];
                }

                return sprintf(...array_merge([static::$messages[$key]], $arguments));
            }
        }
    ';
}

// Here the usage starts :)
$bundle_settings = [
    'bundle' => 'quack.php',
    'resources' => [
        new ResourceBuffer(getLocalesBuffer())
    ]
];

bundle($bundle_settings);

