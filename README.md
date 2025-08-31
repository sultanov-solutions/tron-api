# TRON API
A PHP API for interacting with the Tron Protocol

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

## Install

```bash
composer require sultanov-solutions/tron-api
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
**Tron(TRX)**: TRWBqiqoFZysoAeyR1J35ibuyc8EvhUAoY

