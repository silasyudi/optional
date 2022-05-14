# Optional

[![Tests](https://github.com/silasyudi/optional/actions/workflows/tests.yml/badge.svg)](https://github.com/silasyudi/optional/actions/workflows/tests.yml)
[![Maintainability](https://api.codeclimate.com/v1/badges/22aefd9a146abde68afd/maintainability)](https://codeclimate.com/github/silasyudi/optional/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/22aefd9a146abde68afd/test_coverage)](https://codeclimate.com/github/silasyudi/optional/test_coverage)

Portabilidade da classe `java.util.Optional<T>` do Java para PHP, atualizado com recursos do Java 11.

## Sumário
- [Idioma / Language](#idioma--language)
- [Instalação](#instalao)
- [Pré-requisitos](#pr-requisitos)
- [Funcionalidades](#funcionalidades)
- [Diferenças](#diferenas)

## Idioma / Language

Read the English :us: version [here](README.md).

## Instalação

```sh
composer require silasyudi/optional
```

## Pré-requisitos

- PHP 7.4+
- Composer

## Funcionalidades

A classe Optional encapsula um valor e habilita você a realizar várias opções sobre ele. 

### Exemplo sem Optional:

```php
    /** @var Entity|null $entity */
    $entity = $this->repository->find($id);

    if (!$entity) {
        throw new SomeException();
    }

    ...
```

### Exemplo com Optional:

```php
    /** @var SymfonyBoot\Optional $optional */
    $optional = $this->repository->find($id);
    $entity = $optional->orElseThrow(new SomeException());
    ...
```

## Diferenças

Algumas diferenças não puderam ser evitadas devido às particularidades existentes em cada linguagem. As principais foram:

* `Optional.stream()` do Java não foi importado para este pacote, devido a não existir algo similar no PHP e por existirem
outros métodos `map`, `flatMap` and `filter` que podem ser utilizados.
* `Optional.hashCode()` não foi importado para este pacote.
* `NullPointerException` e `NoSuchElementException` do Java foi substituído por `OptionalInvalidStateException` 
quando o objeto Optional não pode ser vazio e por `TypeError` quando parâmetros `callable` receberem `null`. 
* `Optional.orElseThrow` no Java 11 é sobrecarregado, e pode ser usado sem parâmetros ou com objeto Supplier.
Neste pacote, este método espera um objeto Throwable ou `null`. 
* `Consumer`, `Function`, `Predicate` e `Supplier` foram importados como `callable`.
