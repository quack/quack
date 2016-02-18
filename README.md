# Urano Lang

> A Rust-based programming language with gradual and duck typing that targets PHP.

![Urano](./resource/logo.png)

Urano is a programming language that targets PHP, based in Rust syntax and TypeScript type system, with support
to gradual and duck typing, with the following features:

- Type safe, with primitive types and extensible type system;
- Support for protocols and type derivations;
- First-class type system;
- Multi-paradigm (functional, imperactive, object oriented);
- Operator overloading;
- Code optimization.

#### Hello World

```swift
def main: void [
  let hello: string :- "Hello world!"
  print hello
]
```

```php
function Main() {
  echo "Hello world!";
}
```

#### Factorial

```swift
-: imperative
def fact [n] [
  let i: int
  let fact: int :- 1
  for i :- 1 while i <= n do [
    fact :- fact * i
  ]
  <<< fact
]

-: tail_call_recursion
def fact [n] [
  <<< if n = 0 then 1 else [n * fact[n - 1]]
]

-: compressed
def fact! if &(= 0) then 1 else &(* fact[&0 - 1])


```

```php
<?php

function Fact(n) {
  return n === 0
    ? 1
    : n * Fact(n - 1);
}
```

#### While-else control structure

```swift
while [let pokemon: number = ^int readline["Pick a pokémon"; PHP_EOL]] <= 4
  match pokemon [
    1 = "You chose charmander!"
    2 = "You chose squirtle!"
    3 = "You chose bulbasaur!"
    _ = "You chose pikachu!"
  ]
else
  print "Invalid pokémon!"
```

### Execute tests

`make test module=module_name`

**Modules**

| `lexer` |
-----------
