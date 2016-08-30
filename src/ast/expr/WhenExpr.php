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
namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\NativeQuackType;
use \QuackCompiler\Types\Type;

class WhenExpr extends Expr
{
    public $cases;

    public function __construct($cases)
    {
        $this->cases = $cases;
    }

    public function format(Parser $parser)
    {
        $source = 'when';
        $source .= PHP_EOL;

        $parser->openScope();

        for ($i = 0, $l = sizeof($this->cases); $i < $l; $i++) {
            $obj = $this->cases[$i];

            $source .= $parser->indent();
            $source .= '| ';

            if (null !== $obj->condition) {
                $source .= $obj->condition->format($parser);
                $source .= ' -> ';
            } else {
                $source .= 'else ';
            }

            $source .= $obj->action->format($parser);

            if ($i + 1 !== $l) {
                $source .= ';';
                $source .= PHP_EOL;
            }
        }

        $parser->closeScope();

        $source .= PHP_EOL;
        $source .= $parser->indent();
        $source .= 'end';

        return $this->parenthesize($source);
    }

    public function injectScope(&$parent_scope)
    {
        foreach (array_map(function ($case) {
            return (object) $case;
        }, $this->cases) as $case) {
            if (null !== $case->condition) {
                $case->condition->injectScope($parent_scope);
            }
            $case->action->injectScope($parent_scope);
        }
    }

    public function getType()
    {
        $conds = 1;
        $type = null;
        foreach (array_map(function ($case) {
            return (object) $case;
        }, $this->cases) as $case) {

            // Assert all conditions are booleans
            if (null !== $case->condition) {
                $condition_type = $case->condition->getType();
                if (NativeQuackType::T_BOOL !== $condition_type->code) {
                    throw new ScopeError([
                        'message' => "Expected condition {$conds} of `when' to be boolean. Got `$condition_type'"
                    ]);
                }
            }

            $action_type = $case->action->getType();
            $type = null === $type
                ? $action_type
                : Type::getBaseType([$type, $action_type]);
            $conds++;
        }

        return $type;
    }
}
