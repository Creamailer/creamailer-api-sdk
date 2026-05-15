<?php

declare(strict_types=1);

namespace Creamailer\Tests\Support;

use Creamailer\Http\Transport;

class FakeTransport implements Transport
{
    /** @var array<int, array{status: int, body: string}> */
    private array $queuedResponses = [];

    /** @var array<int, array{method: string, url: string, headers: array<int, string>, body: string}> */
    public array $sentRequests = [];

    /**
     * @param  array<string, mixed>|string  $body
     */
    public function queue(int $status, array|string $body): self
    {
        $this->queuedResponses[] = [
            'status' => $status,
            'body' => is_array($body) ? json_encode($body, JSON_THROW_ON_ERROR) : $body,
        ];

        return $this;
    }

    /**
     * @param  array<int, string>  $headers
     * @return array{status: int, body: string}
     */
    public function send(string $method, string $url, array $headers, string $body): array
    {
        $this->sentRequests[] = [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
        ];

        if ($this->queuedResponses === []) {
            return ['status' => 200, 'body' => '{}'];
        }

        return array_shift($this->queuedResponses);
    }

    /**
     * @return array{method: string, url: string, headers: array<int, string>, body: string}|null
     */
    public function lastRequest(): ?array
    {
        $count = count($this->sentRequests);

        return $count > 0 ? $this->sentRequests[$count - 1] : null;
    }

    public function headerValue(string $name): ?string
    {
        $last = $this->lastRequest();

        if ($last === null) {
            return null;
        }

        foreach ($last['headers'] as $header) {
            if (stripos($header, $name.':') === 0) {
                return trim(substr($header, strlen($name) + 1));
            }
        }

        return null;
    }
}
