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

/* Statements */
Stmt "statement"
  = Identifier


/* Keywords */
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

StaticToken "static"
  = "static" !IdentRest

StringToken "string"
  = "string" !IdentRest

TrueToken "true"
  = "true" !IdentRest

TryToken "try"
  = "try" !IdentRest

XorToken "xor"
  = "xor" !IdentRest

YieldToken "yield"
  = "yield" !IdentRest

/* Identifier */
Identifier "identifier"
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
