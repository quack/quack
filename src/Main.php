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

    foreach ($compilation_list as $file) {
        try {
            if (!file_exists($file)) {
                throw new \Exception("File [$file] not found");
            }

            $scope = new Scope();
            $lexer = new Tokenizer(file_get_contents($file));
            $parser = new TokenReader($lexer);
            $parser->parse();

            if (!$disable_scope) {
                $parser->ast->injectScope($scope);
            }

            if (!$disable_typechecker) {
                $parser->ast->runTypeChecker();
            }

            echo $parser->format();
        } catch (Exception $e) {
            echo $e;
            exit(1);
        }
    }

    return;
}

$console = new Console(STDIN, STDOUT, STDERR);
$console->subscribe([
    0x7F => 'handleBackspace',
    0xC  => 'handleClearScreen',
    0x1B => [
        0x4F => [
            0x46 => 'handleEnd',
            0x48 => 'handleHome'
        ],
        0x5B => [
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
$repl->start();
