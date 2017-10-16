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
[x] Implement simplification on let, const, fn signature, lambda signature or a function to prove type paths

## Very important

[x] Declare operators in objects in expressions
[x] Declare operators in objects in types
[x] Access operators by `.' from objects in expressions
[ ] Implement algebraic data types
[ ] Resolve transpilation of compile-time type declarations
[x] Pattern match parsing
[ ] Resolve type and scope for matching variables in pattern match
[ ] Support literals in pattern match
[ ] Support placeholders on pattern match
[ ] Define full grammar for pattern match, support list destructuring too


## Examples

```ebnf
x
1
atom
@post
string
number
%{x: %{y: number}, y:number}
obj: %{}
Bool
#(string, number)
{x, ...xs}
{}
{a}
```
