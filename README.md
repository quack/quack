<p align="center">
  <a href="">
    <img alt="Logo" src="./resource/logo.png" width="600px">
  </a>
</p>

<p align="center">

  <a href="https://gitter.im/quack/quack?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge"><img alt="Join the chat at https://gitter.im/quack/quack" src="https://badges.gitter.im/quack/quack.svg"></a>

  <a href="https://travis-ci.org/quack/quack"><img alt="Build Status" src="https://travis-ci.org/quack/quack.svg?branch=master"></a>

  <a href="https://codeclimate.com/github/quack/quack"><img alt="Code Climate" src="https://codeclimate.com/github/quack/quack/badges/gpa.svg"></a>

  <a href="https://codeclimate.com/github/quack/quack/coverage"><img alt="Test Coverage" src="https://codeclimate.com/github/quack/quack/badges/coverage.svg"/></a>

  <a href="https://codeclimate.com/github/quack/quack"><img alt="Issue Count" src="https://codeclimate.com/github/quack/quack/badges/issue_count.svg"/></a>

</p>

Quack is a programming language that targets PHP, based in Rust syntax and TypeScript type system, with support
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
  <<< n = 0 ? 1 : [n * fact[n - 1]]
]

-: compressed
def fact!  &(= 0) ? 1 : &(* fact[&0 - 1])


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

| `lexer` | `ast` | `parser` |
|---------|-------|----------|
