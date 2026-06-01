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
    public function map(ClientException $exception): RodiumAIException
    {
        $response = $exception->getResponse();
        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);
        $message = is_array($body) ? ($body['error']['message'] ?? $exception->getMessage()) : $exception->getMessage();

        return match ($statusCode) {
            401 => new UnauthorizedException($message, $statusCode, $exception, $body),
            402 => new InsufficientCreditsException($message, $statusCode, $exception, $body),
            429 => new RateLimitException($message, $statusCode, $exception, $body),
            422 => new ValidationException($message, $statusCode, $exception, $body),
            default => new RodiumAIException($message, $statusCode, $exception, $body),
        };
    }
}
