# Store in the AST the position of the symbols

- Why?
Because we need to provide good error messages and, in order to show the error location,
we need this information.

- How?
Each node must have the following additional information:

```haskell
type SourceMappable :- %{
    start: number,
    end: number,
    loc: %{
        start: %{ line: number, column: number },
        end: %{ line: number, column: number }
    }
}
```

# Create an AST viewer

- Why?
Because currently is very hard to debug AST nodes

- How?
We can generate JSON from the AST and use a formatter tool. Requires research.

# Implement return type getter for imperative blocks of code

- Why?
Because we currently are able to infer only types from pure expressions

# Implement FromStmt

- Why?
Because we need to allow importing modules and types

# Implement parametric polymorphism
# Implement correct pattern matching parser based on EBNF
# Implement optimizer module core
# Implement code generator core
# Implement PHP backend
# Isolate errors in new module
# Make lexer accept whitespaces and comments, generate tokens and not conflict with parser
