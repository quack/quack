Tasks in Quack Compiler

Sprint 1:

- [x] Remove blueprints
- [x] Remove members
- [x] Short method syntax
- [x] Classes over traits
- [x] Shapes over structs
- [x] Parenthesis on function calls and definitions
- [x] Impl with implicit `fn'
- [x] Change syntax for comments
- [x] Isolate declarations from simple statements
- [x] New syntax for lambda-expr

Sprint 2:

- [x] Run typechecker on TryStmt
- [x] Run typechecker on ContinueStmt
- [x] Run typechecker on BreakStmt
- [x] Run typechecker on SwitchStmt and CaseStmt
- [x] Run typechecker on ElifStmt
- [x] Run typechecker on ForeachStmt
- [x] Run typechecker on LabelStmt
- [x] Run typechecker on ForStmt
- [x] Create default class TypeError and replace some of ScopeError that are specific for types
- [x] Skip typechecker over ModuleStmt and OpenStmt
- [x] Inject type for variable in ForStmt
- [x] Inject type for variable in ForeachStmt
- [x] Remove difference between number types for type checking
- [x] Traverse AST first time to get declarations and bind to scope

- [ ] Run typechecker for RaiseStmt, ensure string
- [ ] Implement parser for type signatures
- [ ] Run and implement type checker and reasoning for FnStmt
- [ ] Record, in the AST, the positions of the symbols, in order to give better error messages
- [ ] Run typechecker and see type rules for bluerprints, traits, structs and impls
- [ ] Assert the context of ReturnStmt and pass the expected return type to it when inside functions

# Type System

- [ ] Implement parenthesize on types
- [ ] Move __toString to format functions, receiving the parser because sometimes it is necessary (in types)
- [ ] Types must know instances (shapes and classes) for named types, and must ensure they exist
- [ ] When there is a type declaration, we should output it on format
- [ ] Isolate statement parsers, with a main class that instantiates and uses them and has a hash of parsers
- [ ] Bind type to symbol when it is passed (such as in let x :: string)
- [ ] Create a TypeSyntaxError (TSY), that will be throw on type syntax error
- [ ] Create comparator function (deep comparison) on types
- [ ] Replace current type returns and verifications by new ones. Type.php dies