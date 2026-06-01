<?php

namespace RodiumAI\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Base exception for RodiumAI API errors.
 *
 * @see https://www.rodiumai.io/docs/api/errors
 */
class RodiumAIException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        private readonly ?array $responseBody = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /** Decoded JSON error body from the API, when available. */
    public function responseBody(): ?array
    {
        return $this->responseBody;
    }
}
