<?php

namespace QuackCompiler\Ast;

use \QuackCompiler\Parser\Parser;

interface Node
{
  function format(Parser $parser);
}
