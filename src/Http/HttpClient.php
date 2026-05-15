<?php

declare(strict_types=1);

namespace Creamailer\Http;

use Creamailer\Exceptions\ApiException;
use Creamailer\Exceptions\AuthenticationException;
use Creamailer\Exceptions\AuthorizationException;
use Creamailer\Exceptions\NotFoundException;
use Creamailer\Exceptions\RateLimitException;
use Creamailer\Exceptions\ServerException;
use Creamailer\Exceptions\TransportException;
use Creamailer\Exceptions\ValidationException;

class HttpClient
{
    private const API_PATH_PREFIX = 'v2/api';

    public function __construct(
        private string $accessToken,
        private string $sharedSecret,
        private string $baseUrl,
        private Transport $transport,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * @param  array<string, scalar|null>  $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, $query, null);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function post(string $path, array $body = []): array
    {
        return $this->request('POST', $path, [], $body);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function put(string $path, array $body = []): array
    {
        return $this->request('PUT', $path, [], $body);
    }

    /**
     * @param  array<string, scalar|null>  $query
     * @return array<string, mixed>
     */
    public function delete(string $path, array $query = []): array
    {
        return $this->request('DELETE', $path, $query, null);
    }

    /**
     * @param  array<string, scalar|null>  $query
     * @param  array<string, mixed>|null  $body
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, array $query, ?array $body): array
    {
        $path = ltrim($path, '/');
        $fullPath = self::API_PATH_PREFIX.'/'.$path;

        $queryString = $this->buildQueryString($query);
        $bodyString = $body !== null ? json_encode($body, JSON_THROW_ON_ERROR) : '';
        $timestamp = (string) time();

        $signature = $this->signature($fullPath, $queryString, $bodyString, $timestamp);
        $url = $this->baseUrl.'/'.$fullPath.$queryString;

        $headers = [
            'X-Access-Token: '.$this->accessToken,
            'X-Request-Signature: '.$signature,
            'X-Request-Timestamp: '.$timestamp,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $response = $this->transport->send($method, $url, $headers, $bodyString);

        return $this->parseResponse($response['status'], $response['body']);
    }

    /**
     * @param  array<string, scalar|null>  $query
     */
    private function buildQueryString(array $query): string
    {
        $filtered = array_filter($query, static fn ($value) => $value !== null);

        if ($filtered === []) {
            return '';
        }

        return '?'.http_build_query($filtered);
    }

    private function signature(string $path, string $queryString, string $body, string $timestamp): string
    {
        $data = $this->baseUrl.'/'.$path.$queryString.$body.$timestamp;

        return hash_hmac('sha256', $data, $this->sharedSecret);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseResponse(int $status, string $body): array
    {
        $decoded = $body !== '' ? json_decode($body, true) : [];

        if (! is_array($decoded)) {
            $decoded = ['message' => $body];
        }

        if ($status >= 200 && $status < 300) {
            /** @var array<string, mixed> $decoded */
            return $decoded;
        }

        $message = is_string($decoded['message'] ?? null)
            ? $decoded['message']
            : sprintf('Unexpected response (HTTP %d).', $status);

        /** @var array<string, mixed> $decoded */
        throw match (true) {
            $status === 401 => new AuthenticationException($message, $status, $decoded),
            $status === 403 => new AuthorizationException($message, $status, $decoded),
            $status === 404 => new NotFoundException($message, $status, $decoded),
            $status === 422 => new ValidationException($message, $status, $decoded),
            $status === 429 => new RateLimitException($message, $status, $decoded),
            $status >= 500 => new ServerException($message, $status, $decoded),
            $status === 0 => new TransportException($message),
            default => new ApiException($message, $status, $decoded),
        };
    }
}
