<?php

declare(strict_types=1);

namespace Creamailer\Tests;

use Creamailer\Client;
use Creamailer\Exceptions\AuthenticationException;
use Creamailer\Exceptions\NotFoundException;
use Creamailer\Exceptions\RateLimitException;
use Creamailer\Exceptions\ValidationException;
use Creamailer\Tests\Support\FakeTransport;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    private FakeTransport $transport;

    private Client $client;

    protected function setUp(): void
    {
        $this->transport = new FakeTransport;
        $this->client = new Client(
            accessToken: 'test-token',
            sharedSecret: 'test-secret',
            baseUrl: 'https://api.example.com',
            transport: $this->transport,
        );
    }

    public function test_ping_returns_response(): void
    {
        $this->transport->queue(200, ['message' => 'Connection successful.']);

        $result = $this->client->ping();

        $this->assertSame('Connection successful.', $result['message']);
        $this->assertSame('GET', $this->transport->lastRequest()['method']);
        $this->assertStringEndsWith('/v2/api/connection-test', $this->transport->lastRequest()['url']);
    }

    public function test_sends_required_auth_headers(): void
    {
        $this->transport->queue(200, ['data' => []]);

        $this->client->lists()->all();

        $this->assertSame('test-token', $this->transport->headerValue('X-Access-Token'));
        $this->assertNotEmpty($this->transport->headerValue('X-Request-Signature'));
        $this->assertNotEmpty($this->transport->headerValue('X-Request-Timestamp'));
    }

    public function test_signature_uses_hmac_sha256(): void
    {
        $this->transport->queue(200, ['data' => []]);

        $this->client->lists()->all();

        $timestamp = $this->transport->headerValue('X-Request-Timestamp');
        $signature = $this->transport->headerValue('X-Request-Signature');

        $expected = hash_hmac(
            'sha256',
            'https://api.example.com/v2/api/lists'.$timestamp,
            'test-secret'
        );

        $this->assertSame($expected, $signature);
    }

    public function test_signature_includes_query_string(): void
    {
        $this->transport->queue(200, ['data' => []]);

        $this->client->subscribers()->all(123, ['status' => 'active', 'pagesize' => 50]);

        $url = $this->transport->lastRequest()['url'];
        $timestamp = $this->transport->headerValue('X-Request-Timestamp');
        $signature = $this->transport->headerValue('X-Request-Signature');

        $expected = hash_hmac(
            'sha256',
            'https://api.example.com/v2/api/lists/123/subscribers?status=active&pagesize=50'.$timestamp,
            'test-secret'
        );

        $this->assertStringContainsString('status=active', $url);
        $this->assertStringContainsString('pagesize=50', $url);
        $this->assertSame($expected, $signature);
    }

    public function test_signature_includes_request_body(): void
    {
        $this->transport->queue(201, ['data' => []]);

        $this->client->lists()->create(['name' => 'Test List']);

        $body = $this->transport->lastRequest()['body'];
        $timestamp = $this->transport->headerValue('X-Request-Timestamp');
        $signature = $this->transport->headerValue('X-Request-Signature');

        $expected = hash_hmac(
            'sha256',
            'https://api.example.com/v2/api/lists'.$body.$timestamp,
            'test-secret'
        );

        $this->assertSame('{"name":"Test List"}', $body);
        $this->assertSame($expected, $signature);
    }

    public function test_throws_authentication_exception_on_401(): void
    {
        $this->transport->queue(401, ['message' => 'Invalid token.']);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid token.');

        $this->client->lists()->all();
    }

    public function test_throws_not_found_exception_on_404(): void
    {
        $this->transport->queue(404, ['message' => 'List not found.']);

        $this->expectException(NotFoundException::class);

        $this->client->lists()->get(999);
    }

    public function test_throws_validation_exception_on_422_with_errors(): void
    {
        $this->transport->queue(422, [
            'message' => 'The given data was invalid.',
            'errors' => ['name' => ['The name field is required.']],
        ]);

        try {
            $this->client->lists()->create([]);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame(422, $e->getStatusCode());
            $this->assertArrayHasKey('name', $e->getErrors());
        }
    }

    public function test_throws_rate_limit_exception_on_429(): void
    {
        $this->transport->queue(429, ['message' => 'Too Many Requests.']);

        $this->expectException(RateLimitException::class);

        $this->client->lists()->all();
    }
}
