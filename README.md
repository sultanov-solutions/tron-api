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

## Installation

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
- web3p/web3.php ^0.1.6 (installed transitively)

This library is framework-agnostic and compatible with Laravel 12.

## Features

- Standalone and Laravel integration without hard coupling
- Explicit instance creation (no hidden singletons)
- Network presets: mainnet, shasta, nile; custom endpoints support
- Rotating API keys in Laravel (round-robin, persisted via Cache)
- PSR-7 friendly HTTP with Guzzle (core) or Laravel Http client (adapter)
- PHP 8.1+, clean dependencies, CI and release workflows

## Usage

### Standalone

```php
use IEXBase\TronAPI\Tron;

// Defaults to mainnet
$tron = Tron::init();

// Or with options / network preset
$tron = Tron::init([
    'network' => 'shasta',
    // 'api_key' => 'YOUR_TRONGRID_KEY',
    // 'endpoints' => ['full_node' => 'https://...', 'solidity_node' => 'https://...']
]);

$tron->setAddress('TYourAddressBase58OrHex');
$balance = $tron->getBalance(null, true);
```

## Configuration (Laravel)

`config/tron.php` controls default connection and per-connection options:

- `default`: connection name (e.g., `mainnet`)
- `connections.{name}.network`: `mainnet` | `shasta` | `nile` | `custom`
- `connections.{name}.endpoints`: `full_node`, `solidity_node`, `event_server`, `status_page`
- `timeout_ms`, `headers`, `auth` (basic/bearer), `api_key`(s)
- optional: `private_key`, `address`

ENV examples:

```
TRON_DEFAULT=mainnet
TRON_FULLNODE=https://api.trongrid.io
TRON_SOLIDITY_NODE=https://api.trongrid.io
TRON_EVENT_SERVER=https://api.trongrid.io
TRON_API_KEY=key1,key2,key3
TRON_TIMEOUT_MS=30000
```

### Laravel 12

1) Publish config:

```bash
php artisan vendor:publish --tag=tron-config
```

2) Create a fresh connection and use it explicitly:

```php
use IEXBase\TronAPI\Laravel\Facades\Tron; // or app('tron')

// Fresh instance for default connection from config
$tron = Tron::make();

// Or choose connection + override options for this instance
$tron = Tron::make('shasta', ['timeout_ms' => 20000, 'api_key' => 'TEST_KEY']);

$balance = $tron->getBalance(null, true);
```

This library deliberately does not expose a singleton Tron instance in Laravel — every `make(...)` returns a new, predictable instance configured from arguments and config.

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

## API Key Rotation (Laravel)

- Set multiple TronGrid/Tronscan API keys as a comma-separated list in `.env`:

```
TRON_API_KEY=key1,key2,key3
```

- Each `Tron::make(...)` picks the next key in a round-robin fashion and persists the index in Cache.
- You can override rotation per instance by passing an explicit key:

```php
$tron = \IEXBase\TronAPI\Laravel\Facades\Tron::make('mainnet', ['api_key' => 'OVERRIDE_KEY']);
```
