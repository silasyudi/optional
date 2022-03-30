# Optional

[![Tests](https://github.com/symfonyboot/optional/actions/workflows/tests.yml/badge.svg)](https://github.com/symfonyboot/optional/actions/workflows/tests.yml)
[![Maintainability](https://api.codeclimate.com/v1/badges/efa216c89149828022a7/maintainability)](https://codeclimate.com/github/symfonyboot/optional/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/efa216c89149828022a7/test_coverage)](https://codeclimate.com/github/symfonyboot/optional/test_coverage)

Portability of Java's `java.util.Optional<T>` class to PHP, updated with Java 11 features. 

## Summary
- [Language / Idioma](#language--idioma)
- [Instalation](#instalation)
- [Requirements](#requirements)
- [Features](#features)
- [Differences](#differences)

## Language / Idioma

Leia a versão em português :brazil: [aqui](README_PT_BR.md).

## Instalation

```sh
composer require symfonyboot/optional
```

## Requirements

- PHP 7.4+
- Composer

## Features

The Optional class encapsulates a value and can perform various operations on it.

### Example without Optional:

```php
    /** @var Entity|null $entity */
    $entity = $this->repository->find($id);

    if (!$entity) {
        throw new SomeException();
    }

    ...
```

### Example with Optional:

```php
    /** @var SymfonyBoot\Optional $optional */
    $optional = $this->repository->find($id);
    $entity = $optional->orElseThrow(new SomeException());
    ...
```

## Differences

Some differences could not be avoided due to the particularities of each language. The most important are listed below:

* `Optional.stream()` of the Java was not imported into this package, as it doesn't have something similar in PHP and 
already has similar methods in `map`, `flatMap` and `filter`.
* `Optional.hashCode()` was not imported into this package.
* `NullPointerException` e `NoSuchElementException` of the Java was replaced by `OptionalInvalidStateException` 
when the Optional object cannot be empty and `TypeError` when attempting to pass null in `callable` parameters. 
* `Optional.orElseThrow` in Java 11 is overloaded, and expects no parameter or a Supplier parameter.
In this package, this method expects a Throwable object or `null` as parameter. 
* `Consumer`, `Function`, `Predicate` and `Supplier` was imported as `callable`.
