<?php

declare(strict_types=1);

namespace App\Exceptions;

final class PictureUploadException extends \RuntimeException
{
    public function __construct(string $message, private readonly int $status = 422)
    {
        parent::__construct($message);
    }

    /**
     * HTTP status code to return to the client.
     */
    public function status(): int
    {
        return $this->status;
    }
}
