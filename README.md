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

```ruby
fn main
  print "Hello World!"
end
```

```php
function Main() {
  echo "Hello world!";
}
```

#### Factorial

```ruby
-: imperative
fn fact [n] [
  let fact :- 1
  for i from 1 to n do fact :- fact * i
  ^ fact
]

-: tail_call_recursion
fn fact [n]
  ^ n = 0 then 1 else n * fact[ n - 1 ]
end

-: compressed
fn fact! {| &0 = 1 then 1 else &1 * fact[ n - 1 ] } end
```

```php
<?php

function Fact(n) {
  return n === 0
    ? 1
    : n * Fact(n - 1);
}
```

### Execute tests

`make test module=module_name`

**Modules**

| `lexer` | `ast` | `parser` |
|---------|-------|----------|
