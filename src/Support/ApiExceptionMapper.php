<?php

namespace RodiumAI\Support;

use GuzzleHttp\Exception\ClientException;
use RodiumAI\Exceptions\ForbiddenException;
use RodiumAI\Exceptions\InsufficientCreditsException;
use RodiumAI\Exceptions\NotFoundException;
use RodiumAI\Exceptions\RateLimitException;
use RodiumAI\Exceptions\RodiumAIException;
use RodiumAI\Exceptions\UnauthorizedException;
use RodiumAI\Exceptions\ValidationException;

/**
 * Maps HTTP error responses to typed SDK exceptions.
 *
 * Supports OpenAI-shaped and Anthropic-shaped error envelopes.
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
        $parsed = $this->parseErrorBody(is_array($body) ? $body : null);
        $message = $parsed['message'] ?: ($exception->getMessage() ?: $this->messages->unknownError);

        $retryAfter = $this->parseRetryAfter($response->getHeaderLine('Retry-After'));

        return match ($statusCode) {
            401 => new UnauthorizedException(
                $message,
                $statusCode,
                $exception,
                is_array($body) ? $body : null,
                $this->messages->unauthorizedHint,
                $parsed['code'],
                $parsed['type'],
            ),
            402 => new InsufficientCreditsException(
                $message,
                $statusCode,
                $exception,
                is_array($body) ? $body : null,
                $this->messages->insufficientCreditsHint,
                $parsed['code'],
                $parsed['type'],
            ),
            403 => new ForbiddenException(
                $message,
                $statusCode,
                $exception,
                is_array($body) ? $body : null,
                $this->messages->forbiddenHint,
                $parsed['code'],
                $parsed['type'],
            ),
            404 => new NotFoundException(
                $message,
                $statusCode,
                $exception,
                is_array($body) ? $body : null,
                $this->messages->notFoundHint,
                $parsed['code'],
                $parsed['type'],
            ),
            422 => new ValidationException(
                $message,
                $statusCode,
                $exception,
                is_array($body) ? $body : null,
                $this->messages->validationHint,
                $parsed['code'],
                $parsed['type'],
            ),
            429 => new RateLimitException(
                $message,
                $statusCode,
                $exception,
                is_array($body) ? $body : null,
                $this->messages->rateLimitHint,
                $parsed['code'],
                $parsed['type'],
                $retryAfter,
            ),
            default => new RodiumAIException(
                $message,
                $statusCode,
                $exception,
                is_array($body) ? $body : null,
                null,
                $parsed['code'],
                $parsed['type'],
            ),
        };
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @return array{message: ?string, code: ?string, type: ?string}
     */
    private function parseErrorBody(?array $body): array
    {
        if ($body === null) {
            return ['message' => null, 'code' => null, 'type' => null];
        }

        if (($body['type'] ?? null) === 'error' && isset($body['error']) && is_array($body['error'])) {
            return [
                'message' => $body['error']['message'] ?? null,
                'code' => $body['error']['type'] ?? null,
                'type' => 'anthropic_error',
            ];
        }

        $error = $body['error'] ?? null;

        if (! is_array($error)) {
            return ['message' => null, 'code' => null, 'type' => null];
        }

        return [
            'message' => $error['message'] ?? null,
            'code' => $error['code'] ?? null,
            'type' => $error['type'] ?? null,
        ];
    }

    private function parseRetryAfter(string $header): ?int
    {
        $header = trim($header);

        if ($header === '' || ! ctype_digit($header)) {
            return null;
        }

        return (int) $header;
    }
}
