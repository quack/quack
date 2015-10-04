{
  var Urano = {
    list: {
      build: function(x, xs, n) {
        return [x].concat(Urano.list.take(xs, n));
      },
      take: function(xs, n) {
        var result = new Array(xs.length);

        for (var i = 0, len = xs.length; i < len; i++) {
          result[i] = xs[i][n];
        }

        return result;
      },
      opt: function(value) {
        return value !== null ? value : [];
      }
    }
  };
}

Start
  = ___ code:Program {
    return code;
  }

Program
  = body:Body? {
    return {
      type: "Program",
      value: Urano.list.opt(body)
    };
  }

Body
  = x:Stmt xs:(___ Stmt)* ___ {
    return Urano.list.build(x, xs, 1);
  }

StmtList
  = body:Body? {
    return body !== null ? body : [];
  }

/* Statements */
Stmt "statement"
  = BreakStmt
  / IfStmt
  / LoopStmt
  / ReturnStmt
  / WhileStmt
  / Expr

BreakStmt "break stmt"
  = BreakToken __ level:Integer?  {
    return {
      type: "BreakStmt",
      level: level !== null ? level : 1
    };
  }

ElseStmt "else statement"
  = ElseToken __ LeftBracket ___ body:StmtList ___ RightBracket {
    return {
      type: "ElseStmt",
      body: body
    };
  }

ElsifStmt "elsif statement"
  = ElsifToken __ expr:Expr __ LeftBracket ___ body:StmtList ___ RightBracket {
    return {
      type: "ElsifStmt",
      condition: expr,
      body: body
    };
  }

IfStmt "if statement"
  = IfToken __ expr:Expr __ LeftBracket ___ body:StmtList ___ RightBracket
    elsif:(___ elsif:ElsifStmt { return elsif; })*
    _else:(___ _else:ElseStmt { return _else; })? {

    return {
      type: "IfStmt",
      condition: expr,
      then: body,
      elsif: Urano.list.opt(elsif),
      else: _else
    };
  }

LoopStmt "loop statement"
  = LoopToken __ LeftBracket ___ body:StmtList ___ RightBracket {
    return {
      type: "LoopStmt",
      body: body
    };
  }

ReturnStmt "return statement"
  = ReturnToken expr:(__ expr:Expr { return expr })? {
    return {
      type: "ReturnStmt",
      expr: expr
    }
  }

WhileStmt "while statement"
  = WhileToken __ expr:Expr __ LeftBracket ___ body:StmtList ___ RightBracket
    _else:(___ _else:ElseStmt { return _else; })? {
    return {
      type: "WhileStmt",
      condition: expr,
      body: body,
      else: _else
    };
  }

/* Expressions */
Expr "expression"
  = "expr" !IdentRest {
    return ["MAYBE EXPRESSION"];
  }
  / i:Integer {
    return {
      type: "Expr",
      value: i
    }
  }

/* Keywords */
Keyword "reserved word"
   = AliasToken
   / AndToken
   / BoolToken
   / BreakToken
   / ClassToken
   / DeclareToken
   / ElseToken
   / ElsifToken
   / FalseToken
   / FinallyToken
   / ForToken
   / FuncToken
   / IfToken
   / ImportToken
   / InToken
   / IterateToken
   / LetToken
   / LoopToken
   / MatchToken
   / MixedToken
   / ModuleToken
   / NilToken
   / NumberToken
   / ObjectToken
   / OrToken
   / OtherwiseToken
   / ProtocolToken
   / RaiseToken
   / RescueToken
   / ResourceToken
   / ReturnToken
   / StaticToken
   / StringToken
   / TrueToken
   / TryToken
   / WhileToken
   / XorToken
   / YieldToken

AliasToken "alias"
  = "alias" !IdentRest

AndToken "and"
  = "and" !IdentRest

BoolToken "bool"
  = "bool" !IdentRest

BreakToken "break"
  = "break" !IdentRest

ClassToken "class"
  = "class" !IdentRest

DeclareToken "declare"
  = "declare" !IdentRest

ElseToken "else"
  = "else" !IdentRest

ElsifToken "elsif"
  = "elsif" !IdentRest

FalseToken "false"
  = "false" !IdentRest

FinallyToken "finally"
  = "finally" !IdentRest

ForToken "for"
  = "for" !IdentRest

FuncToken "func"
  = "func" !IdentRest

IfToken "if"
  = "if" !IdentRest

ImportToken "import"
  = "import" !IdentRest

InToken "in"
  = "in" !IdentRest

IterateToken "iterate"
  = "iterate" !IdentRest

LetToken "let"
  = "let" !IdentRest

LoopToken "loop"
  = "loop" !IdentRest

MatchToken "match"
  = "match" !IdentRest

MixedToken "mixed"
  = "mixed" !IdentRest

ModuleToken "module"
  = "module" !IdentRest

NilToken "nil"
  = "nil" !IdentRest

NumberToken "number"
  = "number" !IdentRest

ObjectToken "object"
  = "object" !IdentRest

OrToken "or"
  = "or" !IdentRest

OtherwiseToken "otherwise"
  = "otherwise" !IdentRest

ProtocolToken "protocol"
  = "protocol" !IdentRest

RaiseToken "raise"
  = "raise" !IdentRest

RescueToken "rescue"
  = "rescue" !IdentRest

ResourceToken "resource"
  = "resource" !IdentRest

ReturnToken "return"
  = "return" !IdentRest

StaticToken "static"
  = "static" !IdentRest

StringToken "string"
  = "string" !IdentRest

TrueToken "true"
  = "true" !IdentRest

TryToken "try"
  = "try" !IdentRest

WhileToken "while"
  = "while" !IdentRest

XorToken "xor"
  = "xor" !IdentRest

YieldToken "yield"
  = "yield" !IdentRest

/* Operators */
PlusOperator "plus operator"
  = "+"

MinusOperator "minus operator"
  = "-"

/* Integer */
Decimal "decimal"
  = x:[1-9] xs:[0-9]* {
    return parseInt([x].concat(xs).join(""));
  }
  / "0" ![xX] {
    return 0;
  }

Hexadecimal "hexadecimal"
  = "0" [xX] hexa:[0-9a-fA-F]+ {
    return parseInt("0x" + hexa.join(""));
  }

Octal "octal"
  = "0" octal:[0-7]+ {
    return parseInt("0" + octal.join(""), 8);
  }

Integer "integer"
  = op:( PlusOperator / MinusOperator )? t:( Octal / Hexadecimal / Decimal ) {
    // TODO: +- on operators
    return op === null
      ? t
      : op === "+"
        ? +(t)
        : -(t);
  }

/* Identifier */
Identifier "identifier"
  = !Keyword name:IdentName {
    return name;
  }

IdentName
  = x:IdentStart xs:IdentRest* {
    return {
      type: "Identifier",
      value: [x].concat(xs).join("")
    };
  }

IdentStart
  = [a-zA-Z_\x7f-\xff]

IdentRest
  = [a-zA-Z0-9_\x7f-\xff]

/* Matchers */
LeftBracket "left bracket"
  = "{"

RightBracket "right bracket"
  = "}"

LeftSquareBracket "left square bracket"
  = "["

RightSquareBracket "right square bracket"
  = "]"

/* Whitespace and newline */
_ "optional whitespace"
  = [ \t]*

__ "mandatory whitespace"
  = [ \t]+

_n_ "optional newline"
  = [\r\n]*

__n__ "mandatory newline"
  = [\r\n]+

___ "optional newline or whitespace"
  = ( [ \t] / [\r\n] )*

____ "mandatory newline or whitespace"
  = __
  / __n__
