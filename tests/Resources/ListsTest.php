<?php

declare(strict_types=1);

namespace Creamailer\Tests\Resources;

use Creamailer\Client;
use Creamailer\Tests\Support\FakeTransport;
use PHPUnit\Framework\TestCase;

final class ListsTest extends TestCase
{
    private FakeTransport $transport;

    private Client $client;

    protected function setUp(): void
    {
        $this->transport = new FakeTransport;
        $this->client = new Client('token', 'secret', 'https://api.example.com', $this->transport);
    }

    public function test_all(): void
    {
        $this->transport->queue(200, ['data' => [['id' => 1, 'name' => 'A']]]);

        $result = $this->client->lists()->all();

        $this->assertSame('GET', $this->transport->lastRequest()['method']);
        $this->assertStringEndsWith('/v2/api/lists', $this->transport->lastRequest()['url']);
        $this->assertCount(1, $result['data']);
    }

    public function test_get(): void
    {
        $this->transport->queue(200, ['data' => ['id' => 42, 'name' => 'X']]);

        $result = $this->client->lists()->get(42);

        $this->assertStringEndsWith('/v2/api/lists/42', $this->transport->lastRequest()['url']);
        $this->assertSame(42, $result['data']['id']);
    }

    public function test_create(): void
    {
        $this->transport->queue(201, ['data' => ['id' => 99]]);

        $this->client->lists()->create(['name' => 'New', 'language' => 'fi']);

        $this->assertSame('POST', $this->transport->lastRequest()['method']);
        $this->assertSame('{"name":"New","language":"fi"}', $this->transport->lastRequest()['body']);
    }

    public function test_update(): void
    {
        $this->transport->queue(200, ['data' => ['id' => 5]]);

        $this->client->lists()->update(5, ['name' => 'Renamed']);

        $this->assertSame('PUT', $this->transport->lastRequest()['method']);
        $this->assertStringEndsWith('/v2/api/lists/5', $this->transport->lastRequest()['url']);
    }

    public function test_delete(): void
    {
        $this->transport->queue(200, ['message' => 'List removed.']);

        $this->client->lists()->delete(7);

        $this->assertSame('DELETE', $this->transport->lastRequest()['method']);
        $this->assertStringEndsWith('/v2/api/lists/7', $this->transport->lastRequest()['url']);
    }

    public function test_fields(): void
    {
        $this->transport->queue(200, ['data' => []]);

        $this->client->lists()->fields(3);

        $this->assertStringEndsWith('/v2/api/lists/3/fields', $this->transport->lastRequest()['url']);
    }
}
