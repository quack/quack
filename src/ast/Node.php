<?php

namespace UranoCompiler\Ast;

use \UranoCompiler\Parser\Parser;

interface Node
{
  function format(Parser $parser);
}
