<?php

namespace RodiumAI\Exceptions;

class RateLimitException extends RodiumAIException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        ?array $responseBody = null,
        ?string $hint = null,
        ?string $errorCode = null,
        ?string $errorType = null,
        private readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message, $code, $previous, $responseBody, $hint, $errorCode, $errorType);
    }

    public function retryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
