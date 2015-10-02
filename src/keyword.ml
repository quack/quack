module Keyword = struct
  let keywords = [|
    "alias" ;
    "and" ;
    "bool" ;
    "break" ;
    "class" ;
    "declare" ;
    "else" ;
    "elsif" ;
    "false" ;
    "for" ;
    "func" ;
    "if" ;
    "import" ;
    "in" ;
    "iterate" ;
    "let" ;
    "loop" ;
    "match" ;
    "module" ;
    "nil" ;
    "number" ;
    "object" ;
    "or" ;
    "otherwise" ;
    "protocol" ;
    "raise" ;
    "rescue" ;
    "static" ;
    "string" ;
    "true" ;
    "try" ;
    "xor" ;
    "yield"
  |]
  ;;
end

struct x {
  name: string
}

let a: x = +class < stdClass {
  shared name: string = "test"
}

print(a.?name)
