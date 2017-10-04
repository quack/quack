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

        @list ($tag, $value) = $token;
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

        // Kill require and include
        if (in_array($tag, [T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE])) {
            while (true) {
                $token = $tokens[$index++];

                if (';' === $token) {
                    break;
                }
            }
            continue;
        }

        // Heredoc to string
        if (T_START_HEREDOC === $tag) {
            $string = '';

            while (true) {
                $token = $tokens[$index++];

                if (is_string($token)) {
                    $string .= $token;
                    continue;
                }

                list ($tag, $value) = $token;
                if (T_END_HEREDOC === $tag) {
                    break;
                }

                $string .= $value;
            }

            $result .= '"' . addslashes($string) . '"';
            continue;
        }

        $result .= $value;
    }

    if ($has_namespace) {
        $result .= '}';
    } else {
        // When source doesn't belong to a namespace, create entry point
        $result = 'namespace Main { '. $result . '}';
    }

    return trim($result);
}

/**
 * Returns whether a PHP source has declared interfaces or
 * abstract classes, in order to give priority on compilation
 *
 * @param string $source
 * @return boolean
 */
function has_priority($source)
{
    $tokens = token_get_all($source);
    foreach ($tokens as $token) {
        if (is_array($token) && (T_INTERFACE === $token[0] || T_ABSTRACT === $token[0])) {
            return true;
        }
    }

    return false;
}

function console_log($message) {
    echo "\033[01;34m{$message}", PHP_EOL;
}

/**
 * Bundles PHP sources to one single file.
 *
 * @param array $config
 * @return void
 */
function bundle($config)
{
    // Configuration
    $bundle = $config['bundle'];
    $resources = $config['resources'];

    $contents = ['<?php'];
    foreach ($resources as $resource) {
        foreach ($resource->readFiles() as $file => $source) {
            console_log("bundling {$file}");
            $contents[] = minify($source);
        }
    }

    if (!is_dir($bundle['directory'])) {
        mkdir($bundle['directory']);
    }

    $output_path = $bundle['directory'] . '/' . $bundle['filename'];
    file_put_contents($output_path, implode(PHP_EOL, $contents));
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
        return [$this->path => file_get_contents($this->path)];
    }
}

class ResourceDir extends Resource
{
    public function readFiles()
    {
        $abstractions = [];
        $classes = [];
        $handle = opendir($this->path);
        while (false !== ($file = readdir($handle))) {
            if ('.' !== $file && '..' !== $file) {
                $full_path = $this->path . '/' . $file;
                if (is_file($full_path)) {
                    $file_content = file_get_contents($full_path);
                    if (has_priority($file_content)) {
                        $abstractions[$full_path] = $file_content;
                    } else {
                        $classes[$full_path] = $file_content;
                    }
                }
            }
        }
        closedir($handle);
        return array_merge($abstractions, $classes);
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
        return ['anonymous buffer' => $this->buffer];
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
            private static $messages = null;

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
    'bundle' => [
        'directory' => 'bin',
        'filename' => 'quack'
    ],
    'resources' => [
        new ResourceBuffer(getLocalesBuffer()),
        new ResourceDir('src/lexer'),
        new ResourceDir('src/parselets'),
        new ResourceDir('src/parselets/expr'),
        new ResourceDir('src/parselets/types'),
        new ResourceDir('src/parser'),
        new ResourceFile('src/ast/Node.php'),
        new ResourceDir('src/ast/expr'),
        new ResourceDir('src/ast/expr/jsx'),
        new ResourceDir('src/ast/stmt'),
        new ResourceDir('src/ast/types'),
        new ResourceDir('src/scope'),
        new ResourceDir('src/types'),
        new ResourceFile('src/repl/QuackRepl.php')
    ]
];

bundle($bundle_settings);

