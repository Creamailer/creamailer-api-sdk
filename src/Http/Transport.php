<?php

declare(strict_types=1);

namespace Creamailer\Http;

interface Transport
{
    /**
     * Send a raw HTTP request.
     *
     * @param  array<int, string>  $headers Headers as "Header-Name: value" strings.
     * @return array{status: int, body: string}
     */
    public function send(string $method, string $url, array $headers, string $body): array;
}
