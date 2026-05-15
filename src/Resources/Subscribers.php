<?php

declare(strict_types=1);

namespace Creamailer\Resources;

use Creamailer\Http\HttpClient;

class Subscribers
{
    public function __construct(private HttpClient $http) {}

    /**
     * List subscribers on a list with optional filtering and pagination.
     *
     * @param  array{status?: string, date?: string, pagesize?: int, page?: int}  $filters
     * @return array<string, mixed>
     */
    public function all(int $listId, array $filters = []): array
    {
        /** @var array<string, scalar|null> $query */
        $query = $filters;

        return $this->http->get('lists/'.$listId.'/subscribers', $query);
    }

    /**
     * Get a single subscriber by email.
     *
     * @return array<string, mixed>
     */
    public function find(int $listId, string $email): array
    {
        return $this->http->get('lists/'.$listId.'/subscribers/show', ['email' => $email]);
    }

    /**
     * Create a subscriber on a list.
     *
     * @param  array<string, mixed>  $data Must include 'email'. See API docs for all supported fields.
     * @return array<string, mixed>
     */
    public function create(int $listId, array $data): array
    {
        return $this->http->post('lists/'.$listId.'/subscribers', $data);
    }

    /**
     * Update a subscriber on a list. The current email is required in $data['email'].
     *
     * @param  array<string, mixed>  $data Must include 'email'. Use 'new_email' to change email.
     * @return array<string, mixed>
     */
    public function update(int $listId, array $data): array
    {
        return $this->http->put('lists/'.$listId.'/subscribers', $data);
    }

    /**
     * Delete (unsubscribe) a subscriber from a list.
     *
     * @return array<string, mixed>
     */
    public function delete(int $listId, string $email, bool $sendAutoresponders = false): array
    {
        return $this->http->delete('lists/'.$listId.'/subscribers', [
            'email' => $email,
            'send_autoresponders' => $sendAutoresponders ? 1 : null,
        ]);
    }

    /**
     * Bulk-import subscribers to a list (up to 500 per call).
     *
     * @param  array<int, array<string, mixed>>  $subscribers
     * @return array<string, mixed>
     */
    public function import(int $listId, array $subscribers): array
    {
        return $this->http->post('lists/'.$listId.'/subscribers/import', [
            'subscribers' => $subscribers,
        ]);
    }

    /**
     * Get a subscriber's message activity across all lists.
     *
     * @return array<string, mixed>
     */
    public function activity(string $email, ?int $pagesize = null, ?int $page = null): array
    {
        return $this->http->get('subscribers/activity', [
            'email' => $email,
            'pagesize' => $pagesize,
            'page' => $page,
        ]);
    }
}
