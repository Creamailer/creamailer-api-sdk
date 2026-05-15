<?php

declare(strict_types=1);

namespace Creamailer\Exceptions;

use Exception;

class ApiException extends Exception
{
    /** @var array<string, mixed> */
    protected array $response;

    /**
     * @param  array<string, mixed>  $response
     */
    public function __construct(string $message, int $statusCode = 0, array $response = [])
    {
        parent::__construct($message, $statusCode);
        $this->response = $response;
    }

    /**
     * @return array<string, mixed>
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    public function getStatusCode(): int
    {
        return $this->getCode();
    }
}
