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
namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class BlockStmt implements Stmt
{
  public $stmt_list;

  public function __construct($stmt_list)
  {
    $this->stmt_list = $stmt_list;
  }

  public function format(Parser $parser)
  {
    if (sizeof($this->stmt_list) == 0) {
      return "[]\n";
    }

    $string_builder = ["[\n"];
    $parser->openScope();

    foreach ($this->stmt_list as $stmt) {
      $string_builder[] = $parser->indent() . $stmt->format($parser);
    }

    $string_builder[] = $parser->dedent();
    $parser->closeScope();
    $string_builder[] = "]\n";
    return implode($string_builder);
  }

  public function python(Parser $parser)
  {
    if (sizeof($this->stmt_list) == 0) {
      return "pass\n";
    }

    $parser->openScope();

    foreach ($this->stmt_list as $stmt) {
      $string_builder[] = $parser->indent() . $stmt->python($parser);
    }

    $string_builder[] = $parser->dedent();
    $parser->closeScope();
    return implode($string_builder);
  }
}
