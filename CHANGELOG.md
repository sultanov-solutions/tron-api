# Changelog

All notable changes to this project will be documented in this file.

## v5.0.4 - 2025-08-31
- Add optional Laravel integration in-core: Service Provider, Facade, Manager, and Http adapter.
- Explicit instance creation model in Laravel: `Tron::make(...)/app('tron')->make(...)` always returns a fresh instance (no hidden singletons).
- Core factory: `Tron::init([...])` with network presets (mainnet, shasta, nile), custom endpoints, headers, `api_key`, and `auth` options.
- API key rotation (Laravel only): round-robin across keys from `TRON_API_KEY` (comma-separated) or `api_keys` array; persists last index in Cache; per-instance override via `['api_key' => '...']`.
- HTTP semantics: do not send a body with GET requests; only set Guzzle `auth` when credentials are provided.
- Documentation: revamped README (features, usage, Laravel config, key rotation) for clarity and predictability.
- Composer: add Laravel auto-discovery entries; add `illuminate/cache` to `suggest` for rotation persistence.

## v5.0.3 - 2025-08-31
- Compatibility: PSR-7 v2 friendly by switching `web3p/web3.php` to `^0.1.6` (removes `react/http` coupling).
- Tooling & docs: badges, CI/release workflows, CHANGELOG; README updates for install/requirements/usage.

## v5.0.2 - 2025-08-31
- Fix Laravel 12 install conflict by switching `web3p/web3.php` to `^0.1.6` (removes `react/http` → no PSR-7 v1 lock).
- Remove abandoned `comely-io/data-types`; replace internal usage with `bcmath`.
- Raise PHP requirement to `^8.1`.
- Keep Guzzle at `^7.x` for framework compatibility.
- Add explicit extension requirements: `ext-json`, `ext-bcmath`, `ext-mbstring`, `ext-gmp`.
- Add GitHub Actions CI for PHP 8.1–8.3; add release workflow and Dependabot.
- Update README (install, requirements, usage, badges) and package metadata for Laravel 12 compatibility.

## v5.0.1 - 2025-08-30
- Initial 5.x fork release under `sultanov-solutions` vendor.
