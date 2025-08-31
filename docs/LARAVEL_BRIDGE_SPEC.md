# Laravel Bridge Specification (sultanov-solutions/laravel-tron-api)

This document describes how to build a Laravel integration package for the core library `sultanov-solutions/tron-api` without coupling the core to Laravel. Copy this into a new repository as the blueprint for the bridge.

## Package Scope
- Name: `sultanov-solutions/laravel-tron-api`
- Purpose: First‑class Laravel 12 integration (DI bindings, config, Http client adapter, optional caching/retries/logging), while keeping the core library framework‑agnostic.
- Core dependency: `sultanov-solutions/tron-api` (this repo)

## Composer
- require:
  - `php`: `^8.1`
  - `illuminate/support`: `^12.0`
  - `illuminate/http`: `^12.0`
  - `sultanov-solutions/tron-api`: `^5.0`
- autoload (PSR‑4):
  - `SultanovSolutions\\LaravelTronAPI\\` → `src/`
- extra.laravel (auto‑discovery):
  - providers:
    - `SultanovSolutions\\LaravelTronAPI\\TronServiceProvider`
  - aliases (optional, if providing a facade):
    - `"Tron": "SultanovSolutions\\\\LaravelTronAPI\\\\Facades\\\\Tron"`

## Public Surface (Bridge)
- Service Provider: `TronServiceProvider`
  - Publishes config file `config/tron.php`
  - Binds interfaces and singletons in the container
  - Optionally wires a PSR‑3 logger if core exposes one
- Facade (optional): `SultanovSolutions\\LaravelTronAPI\\Facades\\Tron`
  - Resolves the core `IEXBase\\TronAPI\\Tron` singleton from the container
- Console command(s) (optional):
  - `tron:ping` — checks node health and prints basic info

## Container Bindings
- Bind core HTTP abstraction to a Laravel adapter:
  - `IEXBase\\TronAPI\\Provider\\HttpProviderInterface` → `SultanovSolutions\\LaravelTronAPI\\Http\\LaravelHttpProvider`
- Register a singleton of the core `IEXBase\\TronAPI\\Tron` configured from `config/tron.php`:
  - Construct `HttpProvider` instances for `fullNode`, `solidityNode`, `eventServer`
  - Support optional `signServer` and `explorer` if needed
  - Optionally set a private key if present in config/env (use with care)

## HTTP Adapter Responsibilities
- Class: `SultanovSolutions\\LaravelTronAPI\\Http\\LaravelHttpProvider`
- Implements core `HttpProviderInterface`
- Uses `Illuminate\\Http\\Client\\Factory` (or `Http` facade) under the hood
- Base URL: `baseUrl($host)`
- Headers: `withHeaders([...])` from config
- Timeout: `timeout($timeoutMs / 1000)`
- Retries (optional): `retry($times, $sleepMs)` (configurable)
- GET semantics: do NOT send a request body; use query where applicable
- POST semantics: send JSON body
- Response mapping:
  - Decode JSON to array; if body is literal `OK`, return `['status' => 1]`
  - 404 → throw core `NotFoundException`
  - Non‑2xx → throw core `TronException` with a helpful message
- Optional caching layer for idempotent GETs via `Cache` (TTL from config)
- Do not log secrets; mask bearer tokens in logs/events

## Config (`config/tron.php`)
Recommended keys (with sensible defaults):
- `full_node` (string): default `https://api.trongrid.io`
- `solidity_node` (string): default `https://api.trongrid.io`
- `event_server` (string): default `https://api.trongrid.io`
- `status_page` (string): default `/` (used by isConnected)
- `timeout_ms` (int): default `30000`
- `retries` (array): `{ 'times' => 2, 'sleep_ms' => 200 }`
- `headers` (array): default `[]`
- `auth` (array): `{ 'basic' => null, 'bearer' => null }`
- `cache` (array): `{ 'enabled' => false, 'ttl' => 5 }` // only for safe GETs
- `log_channel` (string|null): default `null` // if set, adapter uses this channel
- `private_key` (string|null): default `null` // optional; use with caution
- Environment variables to support:
  - `TRON_FULLNODE`, `TRON_SOLIDITY_NODE`, `TRON_EVENT_SERVER`
  - `TRON_TIMEOUT_MS`, `TRON_RETRIES`, `TRON_HEADERS_JSON`
  - `TRON_BASIC_USER`, `TRON_BASIC_PASS`, `TRON_BEARER_TOKEN`

## Events & Logging (Optional)
- Consider dispatching Laravel events for observability:
  - `TronRequestDispatched` (method, url, payload, headers, trace id)
  - `TronResponseReceived` (status, duration, body size, trace id)
- If `log_channel` configured, log request/response meta (mask secrets)
- Integrate with Telescope automatically if available (optional)

## Testing
- Use `orchestra/testbench` to boot a minimal Laravel app for package tests
- Test cases:
  - Config publishing works and defaults load
  - Container resolves `Tron` and `HttpProviderInterface` bindings
  - Adapter maps GET/POST correctly (no body for GET)
  - Retry/timeout behavior from config is applied
  - 404 throws core `NotFoundException`; non‑2xx throws `TronException`
  - Optional cache behavior for GETs respects TTL and toggle

## CI
- GitHub Actions matrix:
  - OS: ubuntu‑latest
  - PHP: 8.1, 8.2, 8.3
  - Laravel: 12.* (via `illuminate/*` constraints)
- Steps:
  - `composer validate`
  - `composer install`
  - `vendor/bin/phpunit`

## Documentation (README template)
Include:
- What this package does (Laravel adapter for `sultanov-solutions/tron-api`)
- Requirements (PHP 8.1+, Laravel 12)
- Install:
  - `composer require sultanov-solutions/laravel-tron-api`
  - publish config: `php artisan vendor:publish --tag=tron-config`
- Quick start:
  - `Tron::getBalance()` via facade or `app(Tron::class)`
- Configuration keys and env variables
- Notes on retries, timeouts, caching, logging
- Credits: link to core and upstream `iexbase/tron-api`
- Donations (both upstream and maintainer)
- Badges: CI, Packagist version/downloads, PHP, Laravel

## SemVer & Releases
- Follow SemVer; keep bridge’s major version aligned with core’s major where possible
- Tag releases as `vX.Y.Z`; do not retag existing versions
- Enable Packagist auto‑updates via GitHub App or webhook

## Security
- Do not log private keys, bearer tokens, or raw payloads
- Mask secrets in events/logs; opt‑in verbose logging only in non‑prod

## Non‑Goals
- The bridge must not re‑implement business logic from the core library
- No breaking changes to the core’s public API; only adaptation to Laravel

## Nice‑to‑Have (Future)
- Health endpoint diagnostics command (`tron:health`)
- Middleware for injecting correlation IDs into requests
- Rate‑limit/backoff policies configurable per method

---

By keeping the core clean and shipping Laravel glue here, Laravel users get a first‑class experience (Http facade, config, DI, retries, logging, caching) with zero impact on non‑Laravel consumers.
