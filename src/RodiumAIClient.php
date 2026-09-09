<?php

namespace RodiumAI;

use Generator;
use RodiumAI\Data\ChatResponse;
use RodiumAI\Data\EmbeddingResponse;
use RodiumAI\Data\ImageResponse;
use RodiumAI\Data\MessageResponse;
use RodiumAI\Data\ModelCollection;
use RodiumAI\Data\ModelInfo;
use RodiumAI\Data\PricingCollection;
use RodiumAI\Data\ResponsesResponse;
use RodiumAI\Data\TranscriptionResponse;
use RodiumAI\Data\VideoResponse;
use RodiumAI\Data\WalletResponse;
use RodiumAI\Support\ApiExceptionMapper;
use RodiumAI\Support\ChatPayloadBuilder;
use RodiumAI\Support\HttpTransport;
use RodiumAI\Support\RodiumAIMessages;
use RodiumAI\Support\SseStreamReader;

/**
 * HTTP client for the RodiumAI REST API (OpenAI-compatible).
 *
 * @see https://www.rodiumai.io/docs
 */
class RodiumAIClient
{
    private HttpTransport $transport;

    private readonly SseStreamReader $streamReader;

    private readonly RodiumAIMessages $messages;

    private ?string $pendingModel = null;

    private ?float $pendingTemperature = null;

    private ?float $pendingTopP = null;

    private ?int $pendingMaxTokens = null;

    private ?string $pendingSystemPrompt = null;

    public function __construct(
        private readonly string $apiKey,
        private readonly int $timeout = 30,
        private readonly string $defaultModel = 'openai/gpt-4o',
        private readonly string $baseUrl = 'https://api.rodiumai.io/v1',
        ?string $locale = null,
        ?ApiExceptionMapper $exceptionMapper = null,
        ?SseStreamReader $streamReader = null,
        ?HttpTransport $transport = null,
    ) {
        $this->messages = RodiumAIMessages::resolve($locale ?? 'en');
        $exceptionMapper = $exceptionMapper ?? new ApiExceptionMapper($this->messages);
        $this->streamReader = $streamReader ?? new SseStreamReader;
        $this->transport = $transport ?? HttpTransport::create(
            apiKey: $this->apiKey,
            baseUrl: $this->baseUrl,
            timeout: $this->timeout,
            exceptionMapper: $exceptionMapper,
            messages: $this->messages,
        );
    }

    public function model(string $model): static
    {
        $clone = clone $this;
        $clone->pendingModel = $model;

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

    /** Optional — inject system prompt so the model replies in the given language. */
    public function language(string $locale): static
    {
        $instruction = RodiumAIMessages::resolve($locale)->aiResponseInstruction;
        $clone = clone $this;
        $existing = $this->pendingSystemPrompt;
        $clone->pendingSystemPrompt = ($existing === null || $existing === '')
            ? $instruction
            : (str_contains($existing, $instruction) ? $existing : "{$instruction}\n{$existing}");

        return $clone;
    }

    /**
     * @param  array<int, array{role: string, content: string|array<int, mixed>}>|string  $messages
     * @param  array<string, mixed>  $options
     */
    public function chat(array|string $messages, array $options = []): ChatResponse
    {
        $payload = $this->payloadBuilder()->build($messages, $options, stream: false);

        return ChatResponse::fromArray(
            $this->transport->requestJson('POST', 'chat/completions', $payload, $this->requestOptions($options))
        );
    }

    /**
     * @param  array<int, array{role: string, content: string|array<int, mixed>}>|string  $messages
     * @return Generator<string>
     */
    public function stream(array|string $messages, array $options = []): Generator
    {
        $payload = $this->payloadBuilder()->build($messages, $options, stream: true);
        $response = $this->transport->requestStream(
            'chat/completions',
            $payload,
            $this->requestOptions($options),
        );

        yield from $this->streamReader->readTextDeltas($response->getBody());
    }

    /** @see https://www.rodiumai.io/docs/api/models */
    public function models(): ModelCollection
    {
        return ModelCollection::fromArray($this->transport->requestJson('GET', 'models'));
    }

    public function modelInfo(string $id): ModelInfo
    {
        return ModelInfo::fromArray(
            $this->transport->requestJson('GET', 'models/' . rawurlencode($id))
        );
    }

    public function codingModels(): ModelCollection
    {
        return ModelCollection::fromArray(
            $this->transport->requestJson('GET', 'models/coding')
        );
    }

    /**
     * @param  string|list<string>  $input
     * @param  array<string, mixed>  $options
     */
    public function embeddings(string|array $input, array $options = []): EmbeddingResponse
    {
        $payload = array_merge([
            'model' => $options['model'] ?? $this->pendingModel ?? $this->defaultModel,
            'input' => $input,
        ], $this->passthrough($options, ['model', 'input']));

        return EmbeddingResponse::fromArray(
            $this->transport->requestJson('POST', 'embeddings', $payload, $this->requestOptions($options))
        );
    }

    /** @param  array<string, mixed>  $options */
    public function images(array $options): ImageResponse
    {
        $payload = array_merge(
            ['model' => $options['model'] ?? $this->pendingModel ?? $this->defaultModel],
            $this->passthrough($options, ['model'])
        );

        return ImageResponse::fromArray(
            $this->transport->requestJson('POST', 'images/generations', $payload, $this->requestOptions($options))
        );
    }

    /** @param  array<string, mixed>  $options */
    public function videos(array $options): VideoResponse
    {
        $payload = array_merge(
            ['model' => $options['model'] ?? $this->pendingModel ?? $this->defaultModel],
            $this->passthrough($options, ['model'])
        );

        $requestOptions = $this->requestOptions($options);
        $requestOptions['timeout'] = $options['timeout'] ?? max($this->timeout, 600);

        return VideoResponse::fromArray(
            $this->transport->requestJson('POST', 'videos/generations', $payload, $requestOptions)
        );
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function transcribe(string $filePath, array $options = []): TranscriptionResponse
    {
        $fields = [
            'model' => $options['model'] ?? $this->pendingModel ?? $this->defaultModel,
            'language' => $options['language'] ?? null,
            'prompt' => $options['prompt'] ?? null,
            'response_format' => $options['response_format'] ?? null,
            'temperature' => $options['temperature'] ?? null,
        ];

        return TranscriptionResponse::fromArray(
            $this->transport->requestMultipart(
                'audio/transcriptions',
                $fields,
                $filePath,
                options: $this->requestOptions($options),
            )
        );
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function speech(array $options): string
    {
        $payload = array_merge(
            ['model' => $options['model'] ?? $this->pendingModel ?? $this->defaultModel],
            $this->passthrough($options, ['model'])
        );

        return $this->transport->requestBinary(
            'POST',
            'audio/speech',
            $payload,
            $this->requestOptions($options),
        );
    }

    /**
     * Anthropic Messages API drop-in (POST /v1/messages).
     *
     * @param  array<string, mixed>  $options
     */
    public function messages(array $options): MessageResponse
    {
        $transport = $this->transport->withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $options['anthropic_version'] ?? '2023-06-01',
        ]);

        $payload = $this->passthrough($options, ['anthropic_version']);

        return MessageResponse::fromArray(
            $transport->requestJson('POST', 'messages', $payload, $this->requestOptions($options))
        );
    }

    /**
     * Streaming variant of {@see messages()} — yields incremental text deltas
     * from the Anthropic Messages streaming protocol.
     *
     * @param  array<string, mixed>  $options
     * @return Generator<string>
     */
    public function messagesStream(array $options): Generator
    {
        $transport = $this->transport->withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $options['anthropic_version'] ?? '2023-06-01',
        ]);

        $payload = $this->passthrough($options, ['anthropic_version']);
        $payload['stream'] = true;

        $response = $transport->requestStream('messages', $payload, $this->requestOptions($options));

        yield from $this->streamReader->readAnthropicTextDeltas($response->getBody());
    }

    /**
     * OpenAI Responses API passthrough (POST /v1/responses).
     *
     * @param  array<string, mixed>  $options
     */
    public function responses(array $options): ResponsesResponse
    {
        $payload = $this->passthrough($options);
        $payload['stream'] = false;

        return ResponsesResponse::fromArray(
            $this->transport->requestJson('POST', 'responses', $payload, $this->requestOptions($options))
        );
    }

    /**
     * Streaming variant of {@see responses()} — yields incremental text deltas
     * from `response.output_text.delta` events.
     *
     * @param  array<string, mixed>  $options
     * @return Generator<string>
     */
    public function responsesStream(array $options): Generator
    {
        $payload = $this->passthrough($options);
        $payload['stream'] = true;

        $response = $this->transport->requestStream('responses', $payload, $this->requestOptions($options));

        yield from $this->streamReader->readResponsesTextDeltas($response->getBody());
    }

    public function wallet(): WalletResponse
    {
        return WalletResponse::fromArray(
            $this->transport->requestJson('GET', 'wallet')
        );
    }

    public function pricing(?string $model = null): PricingCollection
    {
        $options = $model !== null ? ['query' => ['model' => $model]] : [];

        return PricingCollection::fromArray(
            $this->transport->requestJson('GET', 'pricing', null, $options)
        );
    }

    private function payloadBuilder(): ChatPayloadBuilder
    {
        return new ChatPayloadBuilder(
            defaultModel: $this->defaultModel,
            pendingModel: $this->pendingModel,
            pendingTemperature: $this->pendingTemperature,
            pendingTopP: $this->pendingTopP,
            pendingMaxTokens: $this->pendingMaxTokens,
            pendingSystemPrompt: $this->pendingSystemPrompt,
        );
    }

    /**
     * @param  array<string, mixed>  $options
     * @param  list<string>  $exclude
     * @return array<string, mixed>
     */
    private function passthrough(array $options, array $exclude = []): array
    {
        $exclude = array_merge($exclude, ['timeout']);

        return array_diff_key($options, array_flip($exclude));
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function requestOptions(array $options): array
    {
        $requestOptions = [];

        if (isset($options['timeout'])) {
            $requestOptions['timeout'] = $options['timeout'];
        }

        return $requestOptions;
    }
}
