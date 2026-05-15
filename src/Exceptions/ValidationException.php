<?php

declare(strict_types=1);

namespace Creamailer\Exceptions;

class ValidationException extends ApiException
{
    /**
     * Validation errors returned by the API, keyed by field name.
     *
     * @return array<string, array<int, string>>
     */
    public function getErrors(): array
    {
        $errors = $this->response['errors'] ?? [];

        return is_array($errors) ? $errors : [];
    }
}
