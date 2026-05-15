<?php

declare(strict_types=1);

namespace Creamailer\Tests\Resources;

use Creamailer\Client;
use Creamailer\Tests\Support\FakeTransport;
use PHPUnit\Framework\TestCase;

final class SuppressionsTest extends TestCase
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
        $this->transport->queue(200, ['data' => []]);

        $this->client->suppressions()->all(pagesize: 200);

        $url = $this->transport->lastRequest()['url'];
        $this->assertStringContainsString('/v2/api/suppressions?', $url);
        $this->assertStringContainsString('pagesize=200', $url);
    }

    public function test_create(): void
    {
        $this->transport->queue(201, ['data' => ['email' => 'block@example.com']]);

        $this->client->suppressions()->create('block@example.com');

        $req = $this->transport->lastRequest();
        $this->assertSame('POST', $req['method']);
        $this->assertSame('{"email":"block@example.com"}', $req['body']);
    }

    public function test_delete(): void
    {
        $this->transport->queue(200, ['message' => 'Suppression removed.']);

        $this->client->suppressions()->delete('block@example.com');

        $this->assertSame('DELETE', $this->transport->lastRequest()['method']);
        $this->assertStringContainsString('email=block%40example.com', $this->transport->lastRequest()['url']);
    }
}
