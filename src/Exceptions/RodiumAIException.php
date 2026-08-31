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
        private readonly ?string $hint = null,
        private readonly ?string $errorCode = null,
        private readonly ?string $errorType = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function responseBody(): ?array
    {
        return $this->responseBody;
    }

    public function hint(): ?string
    {
        return $this->hint;
    }

    public function errorCode(): ?string
    {
        return $this->errorCode;
    }

    public function errorType(): ?string
    {
        return $this->errorType;
    }

    public function __toString(): string
    {
        $base = static::class . "({$this->code}): {$this->message}";

        return $this->hint !== null ? "{$base} — {$this->hint}" : $base;
    }
}
