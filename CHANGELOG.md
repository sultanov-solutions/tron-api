# Changelog

All notable changes to this project will be documented in this file.

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

