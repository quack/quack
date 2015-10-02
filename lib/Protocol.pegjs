Start
  = Identifier

/* Keywords */
ProtocolKeyword
  = "protocol" !IdentRest

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

/* Whitespace */
_ "optional whitespace"
  = [ \t]+

__ "mandatory whitespace"
  = [ \t]+

_n_ "optional newline"
  = [\r\n]*

__n__ "mandatory newline"
  = [\r\n]+
