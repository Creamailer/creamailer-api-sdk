# Migrating from v1 to v2

The `2.0.0` release is a clean rewrite targeting the Creamailer API v2. v1 is **not supported** by this version — stay on `^1.0` if you still call the legacy `/v1/api/*` endpoints.

## TL;DR

```php
// v1
use Creamailer\Creamailer;

$client = new Creamailer($token, $secret);
$result = $client->lists()->showMany();
foreach ($result->data as $list) {
    echo $list->name;
}
```

```php
// v2
use Creamailer\Client;
use Creamailer\Exceptions\ApiException;

$client = new Client($token, $secret);

try {
    $result = $client->lists()->all();
    foreach ($result['data'] as $list) {
        echo $list['name'];
    }
} catch (ApiException $e) {
    // typed exception per HTTP status
}
```

## What changed

### Entry-point class

| v1 | v2 |
|----|----|
| `Creamailer\Creamailer` | `Creamailer\Client` |

`Creamailer\Creamailer` has been removed. Replace `use Creamailer\Creamailer;` with `use Creamailer\Client;` and rename the constructor accordingly.

### Constructor

```php
// v1 — defaulted to https://api.cmfile.net/v1/api/
new Creamailer($accessToken, $sharedSecret, $apiUrl = 'https://api.cmfile.net/v1/api/');

// v2 — defaults to https://api.cmfile.net (the v2 path prefix is added internally)
new Client($accessToken, $sharedSecret, $baseUrl = 'https://api.cmfile.net', ?Transport $transport = null);
```

If you previously overrode the URL to point at a staging environment, drop the `/v1/api/` suffix:

```diff
- new Creamailer($token, $secret, 'https://api.example.net/v1/api/');
+ new Client($token, $secret, 'https://api.example.net');
```

### Method names

| v1 | v2 |
|----|----|
| `lists()->showMany()` | `lists()->all()` |
| `lists()->show($id)` | `lists()->get($id)` |
| `lists()->subscribers($id, ...)` | `subscribers()->all($id, [...])` |
| `subscribers()->create($listId, $data)` | `subscribers()->create($listId, $data)` (signature unchanged) |
| `subscribers()->delete($listId, $email)` | `subscribers()->delete($listId, $email)` (unchanged) |
| `suppressions()->show()` | `suppressions()->all()` |

Custom field metadata is now its own endpoint:

```php
// v1: no dedicated method
// v2:
$client->lists()->fields($listId);
```

Bulk import and subscriber activity are entirely new in v2:

```php
$client->subscribers()->import($listId, [
    ['email' => 'a@example.com'],
    ['email' => 'b@example.com', 'update_existing' => true],
]);

$client->subscribers()->activity('contact@example.com', pagesize: 20);
```

### Responses

- **v1**: methods returned `stdClass` objects from `json_decode($body)`.
- **v2**: methods return associative `array`s.

Access fields with `$result['data']` rather than `$result->data`.

### Authentication

- **v1**: signed requests with `sha1($url . $body . $timestamp . $secret)` (not a real HMAC).
- **v2**: signs with `hash_hmac('sha256', $base.'/'.$path.$queryString.$body.$timestamp, $secret)`. The SDK handles this for you — no code change needed, but be aware that the new server requires the HMAC-SHA256 scheme and rejects v1-style signatures.

### TLS

- **v1**: `CURLOPT_SSL_VERIFYPEER` was disabled.
- **v2**: TLS verification is on. You should not need to do anything; if you have a custom CA bundle you can supply a custom `Transport`.

### Error handling

- **v1**: callers had to inspect HTTP status codes manually from the response object.
- **v2**: failed requests throw typed exceptions (`AuthenticationException`, `NotFoundException`, `ValidationException`, `RateLimitException`, etc.) — see [README](readme.md#error-handling).

```php
try {
    $client->subscribers()->create($listId, $data);
} catch (\Creamailer\Exceptions\ValidationException $e) {
    // 422 - $e->getErrors() returns the field => messages map
} catch (\Creamailer\Exceptions\RateLimitException $e) {
    // 429 - back off and retry
} catch (\Creamailer\Exceptions\ApiException $e) {
    // anything else
}
```

### PHP version

- **v1**: PHP 5.6+
- **v2**: PHP 8.0+

## Removed in v2

- The `ping()` method now returns an array (`['message' => 'Connection successful.']`) and throws on failure instead of returning a `success` flag.
- The `Transfer\TransferApi` base class is gone. If you extended it, build a custom `Creamailer\Http\Transport` instead — it's a single-method interface.

## Cheat sheet

If you find yourself frequently translating v1 → v2 method calls in a large codebase, the rough rule is:

| v1 verb | v2 verb |
|---------|---------|
| `show`  | `get`   |
| `showMany` | `all` |
| `add`   | `create` |
| (none for bulk) | `import` |
