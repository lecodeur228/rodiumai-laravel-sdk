<?php

namespace RodiumAI;

use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use RodiumAI\Data\ChatResponse;
use RodiumAI\Data\ModelCollection;
use RodiumAI\Enums\RodiumAIModel;
use RodiumAI\Support\ApiExceptionMapper;
use RodiumAI\Support\ChatPayloadBuilder;
use RodiumAI\Support\ModelIdResolver;
use RodiumAI\Support\SseStreamReader;

/**
 * HTTP client for the RodiumAI REST API (OpenAI-compatible).
 *
 * @see https://www.rodiumai.io/docs
 * @see https://www.rodiumai.io/docs/api/overview
 */
class RodiumAIClient
{
    private Client $http;

    private readonly ModelIdResolver $modelResolver;

    private readonly ApiExceptionMapper $exceptionMapper;

    private readonly SseStreamReader $streamReader;

    private ?string $pendingModel = null;

    private ?float $pendingTemperature = null;

    private ?float $pendingTopP = null;

    private ?int $pendingMaxTokens = null;

    private ?string $pendingSystemPrompt = null;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://api.rodiumai.io/v1',
        private readonly int $timeout = 30,
        private readonly string $defaultModel = 'openai/gpt-4o',
        ?ModelIdResolver $modelResolver = null,
        ?ApiExceptionMapper $exceptionMapper = null,
        ?SseStreamReader $streamReader = null,
    ) {
        $this->modelResolver = $modelResolver ?? new ModelIdResolver;
        $this->exceptionMapper = $exceptionMapper ?? new ApiExceptionMapper;
        $this->streamReader = $streamReader ?? new SseStreamReader;
        $this->http = new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'timeout' => $this->timeout,
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function model(string|RodiumAIModel $model): static
    {
        $clone = clone $this;
        $clone->pendingModel = $this->modelResolver->resolve($model);

        return $clone;
    }

    public function temperature(float $temperature): static
    {
        $clone = clone $this;
        $clone->pendingTemperature = $temperature;

        return $clone;
    }

    public function topP(float $topP): static
    {
        $clone = clone $this;
        $clone->pendingTopP = $topP;

        return $clone;
    }

    public function maxTokens(int $maxTokens): static
    {
        $clone = clone $this;
        $clone->pendingMaxTokens = $maxTokens;

        return $clone;
    }

    public function systemPrompt(string $prompt): static
    {
        $clone = clone $this;
        $clone->pendingSystemPrompt = $prompt;

        return $clone;
    }

    /**
     * @param  array<int, array{role: string, content: string}>|string  $messages
     * @param  array<string, mixed>  $options  Optional: model, temperature, top_p, max_tokens, stop
     */
    public function chat(array|string $messages, array $options = []): ChatResponse
    {
        $payload = $this->payloadBuilder()->build($messages, $options, stream: false);

        try {
            $response = $this->http->post('chat/completions', ['json' => $payload]);
            $data = json_decode($response->getBody()->getContents(), true);

            return ChatResponse::fromArray($data);
        } catch (ClientException $e) {
            throw $this->exceptionMapper->map($e);
        }
    }

    /**
     * @param  array<int, array{role: string, content: string}>|string  $messages
     * @param  array<string, mixed>  $options
     * @return Generator<string>
     */
    public function stream(array|string $messages, array $options = []): Generator
    {
        $payload = $this->payloadBuilder()->build($messages, $options, stream: true);

        try {
            $response = $this->http->post('chat/completions', [
                'json' => $payload,
                'stream' => true,
            ]);

            yield from $this->streamReader->readTextDeltas($response->getBody());
        } catch (ClientException $e) {
            throw $this->exceptionMapper->map($e);
        }
    }

    /** @see https://www.rodiumai.io/docs/api/models */
    public function models(): ModelCollection
    {
        try {
            $response = $this->http->get('models');
            $data = json_decode($response->getBody()->getContents(), true);

            return ModelCollection::fromArray($data);
        } catch (ClientException $e) {
            throw $this->exceptionMapper->map($e);
        }
    }

    private function payloadBuilder(): ChatPayloadBuilder
    {
        return new ChatPayloadBuilder(
            defaultModel: $this->defaultModel,
            modelResolver: $this->modelResolver,
            pendingModel: $this->pendingModel,
            pendingTemperature: $this->pendingTemperature,
            pendingTopP: $this->pendingTopP,
            pendingMaxTokens: $this->pendingMaxTokens,
            pendingSystemPrompt: $this->pendingSystemPrompt,
        );
    }
}
