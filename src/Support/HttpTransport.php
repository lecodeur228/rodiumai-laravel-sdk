<?php

namespace RodiumAI\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use RodiumAI\Exceptions\RodiumAIException;

/**
 * HTTP layer for RodiumAI API requests (JSON, multipart, binary, streaming).
 */
final class HttpTransport
{
    public function __construct(
        private readonly Client $http,
        private readonly ApiExceptionMapper $exceptionMapper,
        private readonly string $apiKey,
    ) {}

    public static function create(
        string $apiKey,
        string $baseUrl,
        int $timeout,
        ?ApiExceptionMapper $exceptionMapper = null,
        ?RodiumAIMessages $messages = null,
    ): self {
        $messages = $messages ?? RodiumAIMessages::resolve('en');
        $exceptionMapper = $exceptionMapper ?? new ApiExceptionMapper($messages);

        $http = new Client([
            'base_uri' => rtrim($baseUrl, '/') . '/',
            'timeout' => $timeout,
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        return new self($http, $exceptionMapper, $apiKey);
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @param  array<string, mixed>  $options  Guzzle request options (timeout, headers, query, …)
     * @return array<string, mixed>
     */
    public function requestJson(string $method, string $path, ?array $body = null, array $options = []): array
    {
        if ($body !== null) {
            $options['json'] = $body;
        }

        $response = $this->send($method, $path, $options);
        $decoded = json_decode($response->getBody()->getContents(), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, string|int|float|null>  $fields
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function requestMultipart(
        string $path,
        array $fields,
        string $filePath,
        string $fileField = 'file',
        array $options = [],
    ): array {
        $multipart = [];

        foreach ($fields as $name => $value) {
            if ($value === null) {
                continue;
            }
            $multipart[] = ['name' => $name, 'contents' => (string) $value];
        }

        $multipart[] = [
            'name' => $fileField,
            'contents' => fopen($filePath, 'r'),
            'filename' => basename($filePath),
        ];

        $options['multipart'] = $multipart;
        unset($options['json']);

        $response = $this->send('POST', $path, $options);
        $decoded = json_decode($response->getBody()->getContents(), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @param  array<string, mixed>  $options
     */
    public function requestBinary(string $method, string $path, ?array $body = null, array $options = []): string
    {
        if ($body !== null) {
            $options['json'] = $body;
        }

        $response = $this->send($method, $path, $options);

        return $response->getBody()->getContents();
    }

    /**
     * @param  array<string, mixed>  $body
     * @param  array<string, mixed>  $options
     */
    public function requestStream(string $path, array $body, array $options = []): ResponseInterface
    {
        $options['json'] = $body;
        $options['stream'] = true;

        return $this->send('POST', $path, $options);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function send(string $method, string $path, array $options = []): ResponseInterface
    {
        try {
            return $this->http->request($method, ltrim($path, '/'), $options);
        } catch (ClientException $e) {
            throw $this->exceptionMapper->map($e);
        } catch (GuzzleException $e) {
            throw new RodiumAIException($e->getMessage(), 0, $e);
        }
    }

    public function withHeaders(array $headers): self
    {
        $config = $this->http->getConfig();
        $merged = array_merge($config['headers'] ?? [], $headers);

        $http = new Client(array_merge($config, ['headers' => $merged]));

        return new self($http, $this->exceptionMapper, $this->apiKey);
    }

    public function getClient(): Client
    {
        return $this->http;
    }
}
