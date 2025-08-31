# TRON API
A PHP API for interacting with the Tron Protocol

[![Tests](https://github.com/sultanov-solutions/tron-api/actions/workflows/tests.yml/badge.svg)](https://github.com/sultanov-solutions/tron-api/actions/workflows/tests.yml)
[![Packagist](https://img.shields.io/packagist/v/sultanov-solutions/tron-api.svg?style=flat-square)](https://packagist.org/packages/sultanov-solutions/tron-api)
[![PHP](https://img.shields.io/packagist/php-v/sultanov-solutions/tron-api.svg?style=flat-square)](composer.json)
[![Laravel](https://img.shields.io/badge/Laravel-12%20compatible-FF2D20?logo=laravel&style=flat-square)](#)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

[![Latest Stable Version](https://poser.pugx.org/sultanov-solutions/tron-api/version)](https://packagist.org/packages/sultanov-solutions/tron-api)
[![Build Status](https://img.shields.io/github/actions/workflow/status/sultanov-solutions/tron-api/tests.yml?style=flat-square&label=build)](https://github.com/sultanov-solutions/tron-api/actions/workflows/tests.yml)
[![Contributors](https://img.shields.io/github/contributors/sultanov-solutions/tron-api.svg?style=flat-square)](https://github.com/sultanov-solutions/tron-api/graphs/contributors)
[![Total Downloads](https://img.shields.io/packagist/dt/sultanov-solutions/tron-api.svg?style=flat-square)](https://packagist.org/packages/sultanov-solutions/tron-api)

## Fork & Credits

This project is a maintained fork of https://github.com/iexbase/tron-api. Full credit to the original authors and contributors of the upstream project. This fork focuses on PHP 8+ and Laravel 12 compatibility, dependency hygiene, CI/release tooling, and small fixes.

## Install

```bash
# latest stable
composer require sultanov-solutions/tron-api

# or pin to the current major/minor
composer require sultanov-solutions/tron-api:^5.0.2
```

## Requirements

- PHP: ^8.1
- Extensions: ext-json, ext-bcmath, ext-mbstring, ext-gmp
- Guzzle 7.x (installed transitively)
- web3p/web3.php ^0.3.2 (installed transitively)

This library is framework-agnostic and compatible with Laravel 12.

## Example Usage

```php
use IEXBase\TronAPI\Tron;
use IEXBase\TronAPI\Provider\HttpProvider;
use IEXBase\TronAPI\Exception\TronException;

$fullNode = new HttpProvider('https://api.trongrid.io');
$solidityNode = new HttpProvider('https://api.trongrid.io');
$eventServer = new HttpProvider('https://api.trongrid.io');

try {
    $tron = new Tron($fullNode, $solidityNode, $eventServer);
} catch (TronException $e) {
    exit($e->getMessage());
}

$tron->setAddress('TYourAddressBase58OrHex');

// Balance
$tron->getBalance(null, true);

// Transfer TRX
var_dump($tron->send('TRecipientAddress', 1.5));

// Generate Address
var_dump($tron->createAccount());

// Get Last Blocks
var_dump($tron->getLatestBlocks(2));

// Change account name (only once)
var_dump($tron->changeAccountName('TYourAddressBase58', 'NewName'));

// Contract
$tron->contract('TContractAddressHere');
```

## Testing

```bash
vendor/bin/phpunit
```

## Donations
If this library is useful for you, you may support the original project or this fork’s maintenance (TRON/TRX addresses):

- Original author (IEXBase): TRWBqiqoFZysoAeyR1J35ibuyc8EvhUAoY
- Maintainer (Sultanov Solutions): TPgGPSJ37t4nFGyYa7TQxwLZFJMvK587QT

## Changelog

See `CHANGELOG.md` for release notes.
