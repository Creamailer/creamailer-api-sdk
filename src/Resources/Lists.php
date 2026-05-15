<?php

declare(strict_types=1);

namespace Creamailer\Resources;

use Creamailer\Http\HttpClient;

class Lists
{
    public function __construct(private HttpClient $http) {}

    /**
     * Get all lists.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->http->get('lists');
    }

    /**
     * Get a single list by ID.
     *
     * @return array<string, mixed>
     */
    public function get(int $listId): array
    {
        return $this->http->get('lists/'.$listId);
    }

    /**
     * Create a new list.
     *
     * @param  array{name: string, language?: string, auto_suppress?: bool}  $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->http->post('lists', $data);
    }

    /**
     * Update an existing list.
     *
     * @param  array{name?: string, language?: string, auto_suppress?: bool}  $data
     * @return array<string, mixed>
     */
    public function update(int $listId, array $data): array
    {
        return $this->http->put('lists/'.$listId, $data);
    }

    /**
     * Delete (deactivate) a list.
     *
     * @return array<string, mixed>
     */
    public function delete(int $listId): array
    {
        return $this->http->delete('lists/'.$listId);
    }

    /**
     * Get custom fields defined for a list.
     *
     * @return array<string, mixed>
     */
    public function fields(int $listId): array
    {
        return $this->http->get('lists/'.$listId.'/fields');
    }
}
