<?php

namespace RodiumAI\Support;

use GuzzleHttp\Exception\ClientException;
use RodiumAI\Exceptions\InsufficientCreditsException;
use RodiumAI\Exceptions\RateLimitException;
use RodiumAI\Exceptions\RodiumAIException;
use RodiumAI\Exceptions\UnauthorizedException;
use RodiumAI\Exceptions\ValidationException;

/**
 * Maps HTTP error responses to typed SDK exceptions.
 *
 * @see https://www.rodiumai.io/docs/api/errors
 */
final class ApiExceptionMapper
{
    public function __construct(
        private readonly RodiumAIMessages $messages,
    ) {}

    public function map(ClientException $exception): RodiumAIException
    {
        $response = $exception->getResponse();
        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);
        $message = is_array($body)
            ? ($body['error']['message'] ?? null)
            : null;
        $message = $message ?: ($exception->getMessage() ?: $this->messages->unknownError);

        return match ($statusCode) {
            401 => new UnauthorizedException($message, $statusCode, $exception, $body, $this->messages->unauthorizedHint),
            402 => new InsufficientCreditsException($message, $statusCode, $exception, $body, $this->messages->insufficientCreditsHint),
            429 => new RateLimitException($message, $statusCode, $exception, $body, $this->messages->rateLimitHint),
            422 => new ValidationException($message, $statusCode, $exception, $body, $this->messages->validationHint),
            default => new RodiumAIException($message, $statusCode, $exception, $body),
        };
    }
}
