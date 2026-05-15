<?php

declare(strict_types=1);

namespace Creamailer\Resources;

use Creamailer\Http\HttpClient;

class Suppressions
{
    public function __construct(private HttpClient $http) {}

    /**
     * List all suppressed email addresses.
     *
     * @return array<string, mixed>
     */
    public function all(?int $pagesize = null, ?int $page = null): array
    {
        return $this->http->get('suppressions', [
            'pagesize' => $pagesize,
            'page' => $page,
        ]);
    }

    /**
     * Add an email to the suppression list.
     *
     * @return array<string, mixed>
     */
    public function create(string $email): array
    {
        return $this->http->post('suppressions', ['email' => $email]);
    }

    /**
     * Remove an email from the suppression list.
     *
     * @return array<string, mixed>
     */
    public function delete(string $email): array
    {
        return $this->http->delete('suppressions', ['email' => $email]);
    }
}
