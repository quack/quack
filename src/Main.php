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
namespace QuackCompiler;

require 'toolkit/QuackToolkit.php';

use \Exception;
use \QuackCompiler\Cli\Console;
use \QuackCompiler\Cli\Croak;
use \QuackCompiler\Cli\Repl;
use \QuackCompiler\Lexer\Tokenizer;
use \QuackCompiler\Parser\TokenReader;
use \QuackCompiler\Scope\Scope;

// Compile file instead of opening interactive mode
if (count($argv) > 1) {
    $disable_typechecker = in_array('--disable-typechecker', $argv, true);
    $disable_scope = in_array('--disable-scope', $argv, true);

    $compilation_list = array_filter($argv, function ($file) {
        return preg_match('/\.qk$/', $file);
    });

    function compile($source, $scope) {
        global $disable_scope, $disable_typechecker;

        $lexer = new Tokenizer($source);
        $parser = new TokenReader($lexer);
        $parser->parse();

        if (!$disable_scope) {
            $parser->ast->injectScope($scope);
        }

        if (!$disable_typechecker) {
            $parser->ast->runTypeChecker();
        }

        return $parser;
    }

    foreach ($compilation_list as $file) {
        try {
            if (!file_exists($file)) {
                throw new Exception("File [$file] not found");
            }

            $scope = new Scope();
            // Prepend Prelude
            $prelude = file_get_contents(realpath(dirname(__FILE__) . '/../lib/prelude.qk'));
            $source = $prelude . PHP_EOL . file_get_contents($file);
            $script = compile($source, $scope);
            // Output only provided source instead of all prelude
            // TODO: This is very ugly. We need to start thinking about modules and export
            $nodes_to_skip = preg_match_all('/^data/m', $prelude) - 1;
            foreach (range(0, $nodes_to_skip) as $index) {
                unset($script->ast->stmt_list[$index]);
            }
            echo $script->format();
        } catch (Exception $e) {
            echo $e;
            exit(1);
        }
    }

    return;
}

$console = new Console(STDIN, STDOUT, STDERR);
$console->subscribe([
    0x0  => 'handleListDefinitionsKey',
    0x1  => 'handleCtrlA',
    0x7F => 'handleBackspace',
    0xC  => 'handleClearScreen',
    0x1B => [
        0x4F => [
            0x46 => 'handleEnd',
            0x48 => 'handleHome'
        ],
        0x5B => [
            0x32 => [
                0x7E => 'handleInsert'
            ],
            0x33 => [
                0x7E => 'handleDelete'
            ],
            0x41 => 'handleUpArrow',
            0x42 => 'handleDownArrow',
            0x43 => 'handleRightArrow',
            0x44 => 'handleLeftArrow'
        ]
    ],
    0x3B => [
        0x35 => [
            0x43 => 'handleCtrlRightArrow',
            0x44 => 'handleCtrlLeftArrow'
        ]
    ]
]);

$repl = new Repl($console, new Croak());
$repl->welcome();
$repl->start(['prelude']);
