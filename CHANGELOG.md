# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.1] — 2026-05-15

### Documentation

- Dedicated **Subscriber Activity (CRM Integration)** section in the README with full response-shape example and an explanation of `web_version_url`.
- Removed internal development URLs from the README and `examples/live-test.php`. Default base URL is now `https://api.cmfile.net` everywhere.

No code changes — same package as 2.0.0.

## [2.0.0] — 2026-05-15

**This is a major rewrite targeting Creamailer API v2.** See [MIGRATION.md](MIGRATION.md) for a v1 → v2 upgrade guide. If you are still calling the legacy `/v1/api/*` endpoints, stay on `^1.0`.

### Added

- New `Creamailer\Client` entry-point class replacing `Creamailer\Creamailer`.
- Bulk subscriber import endpoint: `$client->subscribers()->import($listId, $subscribers)` — up to 500 rows per call.
- Subscriber activity endpoint: `$client->subscribers()->activity($email)` — message history across all lists with `web_version_url` links suitable for CRM integrations.
- Custom-fields metadata endpoint: `$client->lists()->fields($listId)`.
- Typed exception hierarchy (`ApiException` + 7 specific subclasses) — failed requests throw based on HTTP status (401, 403, 404, 422, 429, 5xx, transport).
- `ValidationException::getErrors()` returns the per-field error map for 422 responses.
- Pluggable `Creamailer\Http\Transport` interface; default `CurlTransport` ships with TLS verification **on**.
- `examples/live-test.php` CLI for ad-hoc testing against any environment.

### Changed

- **HMAC-SHA256** request signatures (was an unkeyed SHA-1 hash in v1; not a true HMAC).
- Base URL default is now `https://api.cmfile.net` (no `/v1/api/` suffix — v2 prefix is added by the SDK).
- Responses are returned as associative arrays instead of `stdClass` (`$result['data']`, not `$result->data`).
- TLS verification is enabled (`CURLOPT_SSL_VERIFYPEER = true`).
- PHP requirement: **8.0+** (was 5.6+).
- Method renames for consistency with REST conventions:
  - `lists()->showMany()` → `lists()->all()`
  - `lists()->show($id)` → `lists()->get($id)`
  - `suppressions()->show()` → `suppressions()->all()`
  - `lists()->subscribers($id)` → `subscribers()->all($id)`

### Removed

- `Creamailer\Creamailer` class (use `Creamailer\Client`).
- `Creamailer\Api\TransferApi` base class (extend `Creamailer\Http\Transport` instead).
- The `.json` URL suffix used by v1 endpoints.
- v1 endpoints — this SDK only speaks to v2.

### Security

- Switched from unkeyed `sha1()` to `hash_hmac('sha256', ...)` for request signatures, eliminating the length-extension vulnerability of the v1 scheme.
- TLS verification enabled by default.

## [1.x]

Legacy v1 SDK targeting the CodeIgniter-based `/v1/api/*` endpoints. No longer actively developed. Pin to `^1.0` in `composer.json` if you need it.
