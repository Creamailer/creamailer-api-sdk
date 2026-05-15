<?php

declare(strict_types=1);

namespace Creamailer\Tests\Resources;

use Creamailer\Client;
use Creamailer\Tests\Support\FakeTransport;
use PHPUnit\Framework\TestCase;

final class SubscribersTest extends TestCase
{
    private FakeTransport $transport;

    private Client $client;

    protected function setUp(): void
    {
        $this->transport = new FakeTransport;
        $this->client = new Client('token', 'secret', 'https://api.example.com', $this->transport);
    }

    public function test_all_with_filters(): void
    {
        $this->transport->queue(200, ['data' => []]);

        $this->client->subscribers()->all(10, ['status' => 'active', 'pagesize' => 100]);

        $url = $this->transport->lastRequest()['url'];
        $this->assertStringContainsString('/v2/api/lists/10/subscribers?', $url);
        $this->assertStringContainsString('status=active', $url);
        $this->assertStringContainsString('pagesize=100', $url);
    }

    public function test_find(): void
    {
        $this->transport->queue(200, ['data' => ['id' => 1]]);

        $this->client->subscribers()->find(10, 'a@example.com');

        $url = $this->transport->lastRequest()['url'];
        $this->assertStringContainsString('/v2/api/lists/10/subscribers/show?', $url);
        $this->assertStringContainsString('email=a%40example.com', $url);
    }

    public function test_create(): void
    {
        $this->transport->queue(201, ['data' => []]);

        $this->client->subscribers()->create(10, ['email' => 'b@example.com', 'name' => 'B']);

        $req = $this->transport->lastRequest();
        $this->assertSame('POST', $req['method']);
        $this->assertStringEndsWith('/v2/api/lists/10/subscribers', $req['url']);
        $this->assertSame('{"email":"b@example.com","name":"B"}', $req['body']);
    }

    public function test_update(): void
    {
        $this->transport->queue(200, ['data' => []]);

        $this->client->subscribers()->update(10, ['email' => 'c@example.com', 'new_email' => 'd@example.com']);

        $req = $this->transport->lastRequest();
        $this->assertSame('PUT', $req['method']);
        $this->assertSame('{"email":"c@example.com","new_email":"d@example.com"}', $req['body']);
    }

    public function test_delete_without_autoresponders(): void
    {
        $this->transport->queue(200, ['message' => 'Subscriber deleted.']);

        $this->client->subscribers()->delete(10, 'e@example.com');

        $url = $this->transport->lastRequest()['url'];
        $this->assertStringContainsString('email=e%40example.com', $url);
        $this->assertStringNotContainsString('send_autoresponders', $url);
    }

    public function test_delete_with_autoresponders(): void
    {
        $this->transport->queue(200, ['message' => 'Subscriber deleted.']);

        $this->client->subscribers()->delete(10, 'e@example.com', true);

        $this->assertStringContainsString('send_autoresponders=1', $this->transport->lastRequest()['url']);
    }

    public function test_import(): void
    {
        $this->transport->queue(201, ['data' => []]);

        $this->client->subscribers()->import(10, [
            ['email' => 'a@example.com'],
            ['email' => 'b@example.com'],
        ]);

        $req = $this->transport->lastRequest();
        $this->assertStringEndsWith('/v2/api/lists/10/subscribers/import', $req['url']);
        $this->assertStringContainsString('"subscribers"', $req['body']);
        $this->assertStringContainsString('a@example.com', $req['body']);
    }

    public function test_activity(): void
    {
        $this->transport->queue(200, ['data' => []]);

        $this->client->subscribers()->activity('f@example.com', pagesize: 50);

        $url = $this->transport->lastRequest()['url'];
        $this->assertStringContainsString('/v2/api/subscribers/activity?', $url);
        $this->assertStringContainsString('email=f%40example.com', $url);
        $this->assertStringContainsString('pagesize=50', $url);
    }
}
