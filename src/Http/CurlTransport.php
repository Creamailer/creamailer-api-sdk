<?php

declare(strict_types=1);

namespace Creamailer\Http;

use Creamailer\Exceptions\TransportException;

class CurlTransport implements Transport
{
    public function __construct(
        private int $timeoutSeconds = 30,
        private string $userAgent = 'Creamailer-PHP-SDK/2.0',
    ) {}

    /**
     * @param  array<int, string>  $headers
     * @return array{status: int, body: string}
     */
    public function send(string $method, string $url, array $headers, string $body): array
    {
        $ch = curl_init($url);

        if ($ch === false) {
            throw new TransportException('Failed to initialize cURL.');
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        if ($body !== '') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw new TransportException(
                sprintf('cURL request failed (%d): %s', $errno, $error)
            );
        }

        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $status,
            'body' => is_string($response) ? $response : '',
        ];
    }
}
