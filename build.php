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

# This file is responsible for bundling Quack compiler source files
# together in order to generate one single and small file with all
# Quack compiler stuff. I'm sorry, this was written fastly, so the code
# may not be the best. Take care, here be dragons!

ini_set('error_reporting', E_ALL);

/**
 * Receives a source string and returns it minified. Converts
 * namespaces to bundle-compatible ones, strips comments and
 * converts whitespaces.
 *
 * @param string $source
 * @return string[]
 */
function minify($source, $pure_identifiers)
{
    static $name_map = [];
    static $pointer = 0;

    $index = 0;
    $tokens = token_get_all($source);
    $length = count($tokens);
    $result = '';
    $namespace = '';
    $use = [];

    // Replace pure identifiers
    array_walk($tokens, function (&$token) use ($pure_identifiers, &$name_map, &$pointer) {
        if (is_array($token)
            && T_STRING === $token[0]
            && in_array($token[1], $pure_identifiers, true)) {

            // Register identifier when not exists
            if (!isset($name_map[$token[1]])) {
                $name_map[$token[1]] = '_' . $pointer++;
            }

            // Assign identifier
            $token[1] = $name_map[$token[1]];
        }
    });

    while ($index < $length) {
        $token = $tokens[$index++];

        if (is_string($token)) {
            $no_space_problems = [';', '=', '{', '(', '}'];
            $last = substr($result, -1, 1);
            if ($last === ' ' && in_array($token, $no_space_problems, true)) {
                $result[strlen($result) - 1] = $token;
                continue;
            }

            $result .= $token;
        }

        @list ($tag, $value) = $token;
        // Strip comments and PHP tags
        if (in_array($tag, [T_COMMENT, T_DOC_COMMENT, T_OPEN_TAG, T_CLOSE_TAG], true)) {
            continue;
        }

        // Convert multiple whitespaces and newlines to 1 space
        if (T_WHITESPACE === $tag) {
            $last_char = substr($result, -1, 1);

            if (!in_array($last_char, [
                ';', '{', '.', '=', ',', '?', ':', '<', '>', '<=', '>=', '.=', '+=',
                '<<', '>>', '|'
            ], true)) {
                $result .= ' ';
            }
            continue;
        }

        // Transform namespace *; into namespace {
        if (T_NAMESPACE === $tag) {
            $index++;
            while (true) {
                $token = $tokens[$index++];
                $namespace .= @$token[1];

                if (';' === $token) {
                    break;
                }
            }

            $namespace = trim($namespace);
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

            $result .= '"' . $string . '"';
            continue;
        }

        // Fetch "use" directives
        if (T_USE === $tag && @$tokens[$index + 1][1] === '\\') {
            $name = '';
            while (true) {
                $token = $tokens[$index++];
                $name .= @$token[1];

                if ($token === ';') {
                    break;
                }
            }

            $use[] = trim($name);
            continue;
        }

        $result .= $value;
    }

    if ($namespace === '') {
        $namespace = 'Main';
    }

    return [$namespace, $use, trim($result)];
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
    echo "\033[01;34m{$message}\033[0m", PHP_EOL;
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
    $pure_identifiers = $config['pure_identifiers'];


    $contents = ['<?php'];
    $namespaces = [];
    $using = [];
    foreach ($resources as $resource) {
        foreach ($resource->readFiles() as $file => $source) {
            console_log("bundling {$file}");
            list($namespace, $use, $content) = minify($source, $pure_identifiers);

            if (!isset($namespaces[$namespace])) {
                $namespaces[$namespace] = [];
                $using[$namespace] = [];
            }

            $namespaces[$namespace][] = $content;

            foreach ($use as $item) {
                $using[$namespace][$item] = true;
            }
        }
    }

    foreach ($namespaces as $namespace => $sources) {
        $contents[]  = "namespace $namespace{";
        $result = '';

        foreach ($using[$namespace] as $item => $_) {
            $result .= "use $item;";
        }

        foreach ($sources as $source) {
            $result .= $source;
        }

        $result .= '}';
        $contents[] = $result;
    }

    if (!is_dir($bundle['directory'])) {
        mkdir($bundle['directory']);
    }

    $output_path = $bundle['directory'] . '/' . $bundle['filename'];
    file_put_contents($output_path, implode(' ', $contents));
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
    $strings = json_decode(file_get_contents('src/intl/locales/en_US.json'));
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

/**
 * Returns the bundle for quack.wav. Yes, this is meta-programming
 * using PHP!
 */
function getCroakBuffer()
{
    $packing_method = 'S';
    $croak = realpath(dirname(__FILE__) . '/resource/quack.wav');
    $handle = fopen($croak, 'rb');
    $size = filesize($croak);
    $byte_array = (array) array_values(unpack("$packing_method*", fread($handle, $size)));
    fflush($handle);
    fclose($handle);

    // Binary optimization
    $index = 0;
    $length = count($byte_array);
    $compressed_byte_array = [];
    while ($index < $length) {
        if (0 === $byte_array[$index]) {
            // Skip sets of null bytes, use only one
            while (0 === $byte_array[$index]) {
                $index++;
            }

            $compressed_byte_array[] = 0x0;
        } else {
            $compressed_byte_array[] = $byte_array[$index];
            $index++;
        }
    }

    $serialized_byte_array = json_encode($compressed_byte_array);

    return '
        <?php
        namespace QuackCompiler\Cli;

        class Croak
        {
            private $wav = null;

            public function __construct()
            {
                $bytes = ' . $serialized_byte_array . ';
                $filename = tempnam(sys_get_temp_dir(), "quack");
                $handle = fopen($filename, "w");
                foreach ($bytes as $byte) {
                    fwrite($handle, pack("' . $packing_method . '", $byte));
                }
                fclose($handle);

                $this->wav = $filename;
                unset($size);
                unset($bytes);
            }

            public function play()
            {
                $wav = $this->wav;
                $player = `which aplay` ? "aplay" : "afplay";
                return `$player $wav > /dev/null 2> /dev/null &`;
            }

            public function free()
            {
                unlink($this->wav);
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
        new ResourceBuffer(getCroakBuffer()),
        new ResourceDir('src/lexer'),
        new ResourceDir('src/parselets'),
        new ResourceDir('src/parselets/expr'),
        new ResourceDir('src/parselets/types'),
        new ResourceDir('src/parser'),
        new ResourceFile('src/ast/Node.php'),
        new ResourceDir('src/ast/expr'),
        new ResourceDir('src/ast/stmt'),
        new ResourceDir('src/ast/types'),
        new ResourceDir('src/scope'),
        new ResourceDir('src/types'),
        new ResourceFile('src/cli/Component.php'),
        new ResourceFile('src/cli/Console.php'),
        new ResourceFile('src/cli/Repl.php'),
        new ResourceFile('src/Main.php')
    ],
    'pure_identifiers' => [
        'QuackCompiler',
        'Scope',
        'Types',
        'Ast',
        'Localization',
        'Parser',
        'Tokenizer',
        'TokenReader',
        'NativeQuackType',
        'parenthesize',
        'Attachable',
        'Parselet',
        'GroupTypeParselet',
        'LambdaParselet',
        'Intl',
        'StmtList',
        'Token',
        'Word',
        'PostConditionalStmt',
        'injectScope',
        'Expr',
        'getPrecedence',
        'OperatorType',
        'BinaryOperatorParselet',
        'InfixParselet',
        'PrefixParselet',
        'reserve',
        'Tag',
        'Meta',
        'Stmt',
        'SyntaxError',
        'TypeError',
        'ScopeError',
        'format',
        'closeScope',
        'openScope',
        'LiteralType',
        'Parselets',
        'infixLeft',
        'consume',
        'indent',
        'getMeta',
        'setMeta',
        'readChar',
        'tryMatch',
        'consumeIf',
        '_expr',
        'NumberExpr',
        'Grammar',
        'parse',
        '_innerStmtList',
        '_identifier',
        'BinaryOperatorTypeParselet',
        'ObjectType',
        'getTag',
        'FunctionType',
        'getType',
        'Lexer',
        'isBoolean',
        'Precedence',
        'register',
        'getContent',
        'consumeAndFetch',
        'StringExpr',
        'isEnd',
        'match',
        'runTypeChecker',
        'TYPE_EXPRESSION',
        'TYPE_STATEMENT',
        'ListType',
        'isString',
        'isNumber',
        'getOperatorLexeme',
        'Symbol',
        'GenericType',
        'RegexExpr',
        'ObjectParselet',
        'WhenExpr',
        'OperatorExpr',
        'infixParseletForToken',
        'LiteralParselet',
        'EOFError',
        'MapType',
        'FunctionTypeParselet',
        'LiteralTypeParselet',
        'BlockParselet',
        'AtomExpr',
        'WhereExpr',
        'PostfixOperatorParselet',
        'MemberAccessParselet',
        'FnSignatureStmt',
        'NameParselet',
        'CallExpr',
        'BlockExpr',
        'LetStmt',
        'PrefixOperatorParselet',
        'TernaryParselet',
        'GroupParselet',
        'RangeParselet',
        'PartialFuncParselet',
        'TupleType',
        'TernaryExpr'
    ]
];

bundle($bundle_settings);

