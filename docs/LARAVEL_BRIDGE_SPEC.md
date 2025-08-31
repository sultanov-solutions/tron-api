# Laravel Bridge Specification (sultanov-solutions/laravel-tron-api)

This document describes how to build a Laravel integration package for the core library `sultanov-solutions/tron-api` without coupling the core to Laravel. Copy this into a new repository as the blueprint for the bridge.

## Package Scope
- Name: `sultanov-solutions/laravel-tron-api`
- Purpose: First-class Laravel 12 integration (DI bindings, config, Http client adapter, optional caching/retries/logging), while keeping the core library framework-agnostic.
- Core dependency: `sultanov-solutions/tron-api` (this repo)

## Composer
- require:
  - `php`: `^8.1`
  - `illuminate/support`: `^12.0`
  - `illuminate/http`: `^12.0`
  - `sultanov-solutions/tron-api`: `^5.0`
- autoload (PSR-4):
  - `SultanovSolutions\\LaravelTronAPI\\` > `src/`
- extra.laravel (auto-discovery):
  - providers:
    - `SultanovSolutions\\LaravelTronAPI\\TronServiceProvider`
  - aliases (optional, if providing a facade):
    - `"Tron": "SultanovSolutions\\\\LaravelTronAPI\\\\Facades\\\\Tron"`

## Public Surface (Bridge)
- Service Provider: `TronServiceProvider`
  - Publishes config file `config/tron-connection.php`
  - Binds interfaces and singletons in the container
  - Optionally wires a PSR-3 logger if core exposes one
- Facade (optional): `SultanovSolutions\\LaravelTronAPI\\Facades\\Tron`
  - Resolves fresh core `IEXBase\\TronAPI\\Tron` instances via manager
- Console command(s) (optional):
  - `tron:ping` — checks node health and prints basic info

## Container Bindings
- Bind core HTTP abstraction to a Laravel adapter:
  - `IEXBase\\TronAPI\\Provider\\HttpProviderInterface` > `SultanovSolutions\\LaravelTronAPI\\Http\\LaravelHttpProvider`
- Expose a manager to create fresh Tron instances:
  - `app('tron')->make($name = null, array $override = []): Tron`

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
  - 404 > throw core `NotFoundException`
  - Non-2xx > throw core `TronException` with a helpful message
- Optional caching layer for idempotent GETs via `Cache` (TTL from config)
- Do not log secrets; mask bearer tokens in logs/events

## Config (`config/tron-connection.php`)
Recommended keys (with sensible defaults):
- `default`: connection name
- `connections.{name}.network`: `mainnet` | `shasta` | `nile` | `custom`
- `connections.{name}.endpoints`: `full_node`, `solidity_node`, `event_server`, `status_page`
- `timeout_ms`, `retries`, `headers`, `auth` (basic/bearer)
- `api_key` (string or comma-separated), or `api_keys` (array) — enables rotation
- Optional: `private_key`, `address`
- Environment variables to support:
  - `TRON_FULLNODE`, `TRON_SOLIDITY_NODE`, `TRON_EVENT_SERVER`
  - `TRON_TIMEOUT_MS`, `TRON_RETRIES`, `TRON_HEADERS_JSON`
  - `TRON_BASIC_USER`, `TRON_BASIC_PASS`, `TRON_BEARER_TOKEN`, `TRON_API_KEY`

## Events & Logging (Optional)
- Consider dispatching Laravel events for observability
- If `log_channel` configured, log request/response meta (mask secrets)
- Integrate with Telescope automatically if available (optional)

## Testing
- Use `orchestra/testbench` to boot a minimal Laravel app for package tests
- Test cases:
  - Config publishing works and defaults load
  - Container resolves bindings and manager
  - Adapter maps GET/POST correctly (no body for GET)
  - Retry/timeout behavior from config is applied
  - 404 throws core `NotFoundException`; non-2xx throws `TronException`
  - Optional cache behavior for GETs respects TTL and toggle

## CI
- GitHub Actions matrix:
  - OS: ubuntu-latest
  - PHP: 8.1, 8.2, 8.3
  - Laravel: 12.* (via `illuminate/*` constraints)
- Steps: `composer validate`, `composer install`, `vendor/bin/phpunit`

## Documentation (README template)
Include:
- What this package does (Laravel adapter for `sultanov-solutions/tron-api`)
- Requirements (PHP 8.1+, Laravel 12)
- Install:
  - `composer require sultanov-solutions/laravel-tron-api`
  - publish config: `php artisan vendor:publish --tag=tron-connection-config`
- Quick start:
  - `$tron = Tron::make()` via facade or `app('tron')->make()`
- Configuration keys and env variables
- Notes on retries, timeouts, caching, logging
- Credits: link to core and upstream `iexbase/tron-api`
- Donations (both upstream and maintainer)
- Badges: CI, Packagist version/downloads, PHP, Laravel

## SemVer & Releases
- Follow SemVer; keep bridge’s major version aligned with core’s major where possible
- Tag releases as `vX.Y.Z`; do not retag existing versions
- Enable Packagist auto-updates via GitHub App or webhook

## Security
- Do not log private keys, bearer tokens, or raw payloads
- Mask secrets in events/logs; opt-in verbose logging only in non-prod

## Non-Goals
- The bridge must not re-implement business logic from the core library
- No breaking changes to the core’s public API; only adaptation to Laravel

## Nice-to-Have (Future)
- Health endpoint diagnostics command (`tron:health`)
- Middleware for injecting correlation IDs into requests
- Rate-limit/backoff policies configurable per method

---

By keeping the core clean and shipping Laravel glue here, Laravel users get a first-class experience (Http facade, config, DI, retries, logging, caching) with zero impact on non-Laravel consumers.
