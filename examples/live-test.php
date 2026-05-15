<?php

declare(strict_types=1);

/**
 * Live integration test for the Creamailer v2 SDK.
 *
 * Usage:
 *   CREAMAILER_ACCESS_TOKEN=xxx \
 *   CREAMAILER_SHARED_SECRET=yyy \
 *   php examples/live-test.php <command> [args...]
 *
 * Base URL defaults to https://api.cmfile.net. Override with
 * CREAMAILER_BASE_URL if needed.
 *
 * Commands:
 *   ping
 *   lists
 *   list-get <listId>
 *   list-create <name>
 *   list-update <listId> <name>
 *   list-delete <listId>
 *   list-fields <listId>
 *   subscribers <listId>
 *   subscriber-find <listId> <email>
 *   subscriber-create <listId> <email> [name]
 *   subscriber-update <listId> <email> <newName>
 *   subscriber-delete <listId> <email>
 *   subscriber-import <listId> <email1>,<email2>,...
 *   activity <email>
 *   suppressions
 *   suppression-add <email>
 *   suppression-remove <email>
 *   all                       Run a full smoke test (read-only).
 */

require __DIR__.'/../vendor/autoload.php';

use Creamailer\Client;
use Creamailer\Exceptions\ApiException;
use Creamailer\Exceptions\ValidationException;

$accessToken = getenv('CREAMAILER_ACCESS_TOKEN') ?: null;
$sharedSecret = getenv('CREAMAILER_SHARED_SECRET') ?: null;
$baseUrl = getenv('CREAMAILER_BASE_URL') ?: 'https://api.cmfile.net';

if (! $accessToken || ! $sharedSecret) {
    fwrite(STDERR, "Missing CREAMAILER_ACCESS_TOKEN or CREAMAILER_SHARED_SECRET env vars.\n");
    exit(1);
}

$client = new Client($accessToken, $sharedSecret, $baseUrl);

$argv = $_SERVER['argv'];
array_shift($argv); // script name
$command = array_shift($argv) ?? 'help';

function dump(string $label, mixed $value): void
{
    echo "\n=== $label ===\n";
    echo json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";
}

function fail(\Throwable $e): void
{
    fwrite(STDERR, sprintf("\n[FAIL] %s: %s\n", $e::class, $e->getMessage()));

    if ($e instanceof ApiException && $e->getResponse() !== []) {
        fwrite(STDERR, json_encode($e->getResponse(), JSON_PRETTY_PRINT)."\n");
    }
    exit(1);
}

try {
    switch ($command) {
        case 'ping':
            dump('ping', $client->ping());
            break;

        case 'lists':
            dump('lists', $client->lists()->all());
            break;

        case 'list-get':
            $listId = (int) ($argv[0] ?? 0);
            dump("list $listId", $client->lists()->get($listId));
            break;

        case 'list-create':
            $name = $argv[0] ?? 'SDK Live Test '.date('Y-m-d H:i:s');
            dump('list created', $client->lists()->create([
                'name' => $name,
                'language' => 'fi',
                'auto_suppress' => false,
            ]));
            break;

        case 'list-update':
            $listId = (int) ($argv[0] ?? 0);
            $name = $argv[1] ?? 'Renamed by SDK '.date('His');
            dump('list updated', $client->lists()->update($listId, ['name' => $name]));
            break;

        case 'list-delete':
            $listId = (int) ($argv[0] ?? 0);
            dump('list deleted', $client->lists()->delete($listId));
            break;

        case 'list-fields':
            $listId = (int) ($argv[0] ?? 0);
            dump('list fields', $client->lists()->fields($listId));
            break;

        case 'subscribers':
            $listId = (int) ($argv[0] ?? 0);
            dump('subscribers', $client->subscribers()->all($listId, ['pagesize' => 5]));
            break;

        case 'subscriber-find':
            $listId = (int) ($argv[0] ?? 0);
            $email = $argv[1] ?? '';
            dump('subscriber', $client->subscribers()->find($listId, $email));
            break;

        case 'subscriber-create':
            $listId = (int) ($argv[0] ?? 0);
            $email = $argv[1] ?? '';
            $name = $argv[2] ?? 'SDK Test User';
            dump('subscriber created', $client->subscribers()->create($listId, [
                'email' => $email,
                'name' => $name,
            ]));
            break;

        case 'subscriber-update':
            $listId = (int) ($argv[0] ?? 0);
            $email = $argv[1] ?? '';
            $name = $argv[2] ?? 'Updated Name';
            dump('subscriber updated', $client->subscribers()->update($listId, [
                'email' => $email,
                'name' => $name,
            ]));
            break;

        case 'subscriber-delete':
            $listId = (int) ($argv[0] ?? 0);
            $email = $argv[1] ?? '';
            dump('subscriber deleted', $client->subscribers()->delete($listId, $email));
            break;

        case 'subscriber-import':
            $listId = (int) ($argv[0] ?? 0);
            $emails = array_filter(explode(',', $argv[1] ?? ''));
            $subscribers = array_map(static fn (string $email) => ['email' => trim($email)], $emails);
            dump('import result', $client->subscribers()->import($listId, $subscribers));
            break;

        case 'activity':
            $email = $argv[0] ?? '';
            dump('activity', $client->subscribers()->activity($email, pagesize: 5));
            break;

        case 'suppressions':
            dump('suppressions', $client->suppressions()->all(pagesize: 5));
            break;

        case 'suppression-add':
            $email = $argv[0] ?? '';
            dump('suppression added', $client->suppressions()->create($email));
            break;

        case 'suppression-remove':
            $email = $argv[0] ?? '';
            dump('suppression removed', $client->suppressions()->delete($email));
            break;

        case 'all':
            dump('ping', $client->ping());
            $lists = $client->lists()->all();
            dump('lists (first 3)', array_slice($lists['data'] ?? [], 0, 3));
            dump('suppressions (first 3)', array_slice($client->suppressions()->all(pagesize: 3)['data'] ?? [], 0, 3));

            $firstListId = $lists['data'][0]['id'] ?? null;
            if ($firstListId !== null) {
                dump("first list fields ($firstListId)", $client->lists()->fields($firstListId));
                dump("first list subscribers ($firstListId, first 3)", array_slice(
                    $client->subscribers()->all($firstListId, ['pagesize' => 3])['data'] ?? [],
                    0,
                    3
                ));
            }
            break;

        case 'help':
        default:
            echo file_get_contents(__FILE__, false, null, 0, 2048);
            break;
    }
} catch (ValidationException $e) {
    fwrite(STDERR, "\n[VALIDATION FAILED] {$e->getMessage()}\n");
    fwrite(STDERR, json_encode($e->getErrors(), JSON_PRETTY_PRINT)."\n");
    exit(1);
} catch (\Throwable $e) {
    fail($e);
}
