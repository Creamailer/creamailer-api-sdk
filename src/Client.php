<?php

declare(strict_types=1);

namespace Creamailer;

use Creamailer\Http\CurlTransport;
use Creamailer\Http\HttpClient;
use Creamailer\Http\Transport;
use Creamailer\Resources\Lists;
use Creamailer\Resources\Subscribers;
use Creamailer\Resources\Suppressions;

class Client
{
    public const DEFAULT_BASE_URL = 'https://api.cmfile.net';

    private HttpClient $http;

    public function __construct(
        string $accessToken,
        string $sharedSecret,
        string $baseUrl = self::DEFAULT_BASE_URL,
        ?Transport $transport = null,
    ) {
        $this->http = new HttpClient(
            $accessToken,
            $sharedSecret,
            $baseUrl,
            $transport ?? new CurlTransport(),
        );
    }

    public function lists(): Lists
    {
        return new Lists($this->http);
    }

    public function subscribers(): Subscribers
    {
        return new Subscribers($this->http);
    }

    public function suppressions(): Suppressions
    {
        return new Suppressions($this->http);
    }

    /**
     * Test API credentials and connectivity.
     *
     * @return array<string, mixed>
     */
    public function ping(): array
    {
        return $this->http->get('connection-test');
    }
}
