[x] Return type for AccessExpr
[x] Return type for ArrayExpr
[x] Return type for AtomExpr
[x] Return type for BlockExpr
[x] Return type for BoolExpr
[x] Return type for CallExpr
[/] Return type for Expr
[x] Return type for LambdaExpr
[x] Return type for MapExpr
[x] Return type for NameExpr
[/] Return type for NewExpr
[x] Return type for NilExpr
[x] Return type for NumberExpr
[x] Return type for ObjectExpr
[x] Return type for OperatorExpr
[/] Return type for PartialFuncExpr
[x] Return type for PostfixExpr
[x] Return type for PrefixExpr
[x] Return type for RangeExpr
[x] Return type for RegexExpr
[x] Return type for StringExpr
[x] Return type for TernaryExpr
[x] Return type for WhenExpr
[x] Return type for WhereExpr

## Must for 0.1.0:

[x] Implement "atom" literal type
[x] Inject self variable for let statement on value, allowing  let x :: number :- x
[x] Implement type merging via intersection (&)
[x] Implement type simplification, where %{x:number} & %{y:string} is %{x:number, y:string}
[x] Call simplification on checks
[x] Analyse statements for type checkings and replace ->code for ->check()
[ ] Implement simplification on let, const, fn signature, lambda signature or a function to prove type paths
[ ] Implement a "type getter" for statement blocks, where we can get the return path in all paths
[ ] Implement ad-hoc polymorphism

## Future work

[ ] Implement runtime pattern matching (Haskell like)
[ ] Type checking must display the stack of comparisons when find wrong types
[ ] Add name property for function type for better error messages
[ ] Support for algebraic data types and type constructors
[ ] Implement parametric polymophism
