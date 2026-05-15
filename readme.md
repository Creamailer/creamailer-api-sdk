# Creamailer API SDK for PHP

Official PHP SDK for the [Creamailer API v2](https://github.com/Creamailer/Creamailer-laravel/blob/master/docs/api-v2.md).

Upgrading from v1? See [MIGRATION.md](MIGRATION.md). Release history: [CHANGELOG.md](CHANGELOG.md).

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Getting Started](#getting-started)
- [Authentication](#authentication)
- [Access Levels & Rate Limiting](#access-levels--rate-limiting)
- [Error Handling](#error-handling)
- [Lists](#lists)
- [Subscribers](#subscribers)
- [Suppressions](#suppressions)
- [Pagination](#pagination)
- [Manual Testing Against a Live API](#manual-testing-against-a-live-api)
- [Testing in Your App](#testing-in-your-app)

## Requirements

- PHP 8.0+
- `ext-curl`, `ext-json`

## Installation

```bash
composer require creamailer/creamailer-api-sdk
```

> **Note:** This SDK targets the Creamailer API **v2**. If you are still using the v1 API, pin the v1 SDK: `composer require creamailer/creamailer-api-sdk:^1.0`.

## Getting Started

You need your `ACCESS_TOKEN` and `SHARED_SECRET` from Creamailer Settings → API.

```php
use Creamailer\Client;

$creamailer = new Client(
    accessToken: getenv('CREAMAILER_ACCESS_TOKEN'),
    sharedSecret: getenv('CREAMAILER_SHARED_SECRET'),
);

$result = $creamailer->ping();
print_r($result); // ['message' => 'Connection successful.']
```

## Authentication

The SDK signs every request with HMAC-SHA256 and a ±5-minute timestamp window. No additional configuration is needed beyond your credentials.

## Access Levels & Rate Limiting

Each API key has an access level configured server-side:

| Level | Permissions |
|-------|-------------|
| `read` | GET requests only — list/find/activity |
| `write` | All requests — create, update, delete, import, suppression management |

A `403 AuthorizationException` is thrown if a `read` key calls a write endpoint.

**Rate limit:** 100 requests per hour per API key. When the limit is hit, the SDK throws `RateLimitException` (HTTP 429). Implement back-off in your integration:

```php
use Creamailer\Exceptions\RateLimitException;

try {
    $client->subscribers()->all($listId);
} catch (RateLimitException $e) {
    sleep(60); // back off and retry later
}
```

## Error Handling

Failed requests throw typed exceptions:

| HTTP | Exception |
|------|-----------|
| 401  | `Creamailer\Exceptions\AuthenticationException` |
| 403  | `Creamailer\Exceptions\AuthorizationException` |
| 404  | `Creamailer\Exceptions\NotFoundException` |
| 422  | `Creamailer\Exceptions\ValidationException` (see `->getErrors()`) |
| 429  | `Creamailer\Exceptions\RateLimitException` |
| 5xx  | `Creamailer\Exceptions\ServerException` |
| network | `Creamailer\Exceptions\TransportException` |
| other | `Creamailer\Exceptions\ApiException` |

```php
use Creamailer\Exceptions\ValidationException;

try {
    $creamailer->lists()->create(['name' => '']);
} catch (ValidationException $e) {
    foreach ($e->getErrors() as $field => $messages) {
        echo "$field: ".implode(', ', $messages).PHP_EOL;
    }
}
```

## Lists

```php
// List all lists
$creamailer->lists()->all();

// Get a single list
$creamailer->lists()->get(123);

// Create
$creamailer->lists()->create([
    'name' => 'Monthly Newsletter',
    'language' => 'fi',
    'auto_suppress' => true,
]);

// Update
$creamailer->lists()->update(123, ['name' => 'Renamed']);

// Delete
$creamailer->lists()->delete(123);

// Get custom fields defined on the list
$creamailer->lists()->fields(123);
```

## Subscribers

```php
// List subscribers (with optional filters)
$creamailer->subscribers()->all(123, [
    'status' => 'active',    // all, active, unsubscribed, bounced, spamreport, deleted
    'pagesize' => 100,
    'page' => 1,
    'date' => '2024-01-01',  // joined after this date
]);

// Find one subscriber by email
$creamailer->subscribers()->find(123, 'subscriber@example.com');

// Create
$creamailer->subscribers()->create(123, [
    'email' => 'new@example.com',
    'name' => 'Jane Smith',
    'company' => 'Acme Inc',
    'send_autoresponders' => true,
    'custom_fields' => [
        'department' => 'Marketing',
    ],
]);

// Update (use 'new_email' to change the email itself)
$creamailer->subscribers()->update(123, [
    'email' => 'old@example.com',
    'new_email' => 'new@example.com',
    'name' => 'Updated Name',
]);

// Delete (unsubscribe)
$creamailer->subscribers()->delete(123, 'subscriber@example.com');

// Bulk import (max 500 per call)
$creamailer->subscribers()->import(123, [
    ['email' => 'a@example.com', 'name' => 'A'],
    ['email' => 'b@example.com', 'name' => 'B', 'update_existing' => true],
]);

// Subscriber activity across all lists (CRM integration)
$creamailer->subscribers()->activity('subscriber@example.com', pagesize: 20);
```

## Suppressions

```php
// List suppressions
$creamailer->suppressions()->all(pagesize: 200);

// Add suppression
$creamailer->suppressions()->create('blocked@example.com');

// Remove suppression
$creamailer->suppressions()->delete('blocked@example.com');
```

## Pagination

Collection endpoints (`subscribers()->all()`, `subscribers()->activity()`, `suppressions()->all()`) return Laravel-style paginated responses. Pass `page` and `pagesize` (max 1000) and iterate:

```php
$page = 1;

do {
    $response = $client->subscribers()->all($listId, [
        'pagesize' => 500,
        'page' => $page,
    ]);

    foreach ($response['data'] as $subscriber) {
        // ... process row
    }

    $lastPage = $response['meta']['last_page'] ?? 1;
    $page++;
} while ($page <= $lastPage);
```

Each response also includes `links.next` (or `null` on the last page) if you prefer to walk by URL.

## Manual Testing Against a Live API

The repo includes [`examples/live-test.php`](examples/live-test.php) for poking at a real API instance (production or development). Set credentials and base URL via env vars, then run individual commands:

```bash
export CREAMAILER_ACCESS_TOKEN=your-dev-token
export CREAMAILER_SHARED_SECRET=your-dev-secret
export CREAMAILER_BASE_URL=https://api.creadevelopment.net

# Quick connectivity check
php examples/live-test.php ping

# Read-only smoke test (ping + first list + first 3 subscribers + suppressions)
php examples/live-test.php all

# Individual endpoints
php examples/live-test.php lists
php examples/live-test.php list-get 123
php examples/live-test.php subscribers 123
php examples/live-test.php subscriber-create 123 test@example.com "Test User"
php examples/live-test.php activity test@example.com
```

Run `php examples/live-test.php` without arguments to see the full command list.

> **Important:** The HMAC signature is computed using the base URL the SDK uses. It must match the `EXTERNAL_API_BASE_URL` configured on the server — otherwise signatures won't match and you'll get `401`. The SDK trims a trailing slash from the configured base URL, so set the server env var **without** a trailing slash (e.g. `EXTERNAL_API_BASE_URL=https://api.creadevelopment.net`, not `.../`).

## Testing in Your App

The SDK uses a `Transport` interface internally, so you can swap in a fake transport in your tests:

```php
use Creamailer\Client;
use Creamailer\Http\Transport;

class StubTransport implements Transport
{
    public function send(string $method, string $url, array $headers, string $body): array
    {
        return ['status' => 200, 'body' => '{"data": []}'];
    }
}

$client = new Client('token', 'secret', 'https://api.cmfile.net', new StubTransport);
```

## License

MIT — see [LICENSE](LICENSE).

## Support

Report issues at [github.com/Creamailer/creamailer-api-sdk/issues](https://github.com/Creamailer/creamailer-api-sdk/issues).
