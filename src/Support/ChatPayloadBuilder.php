<?php

namespace RodiumAI\Support;

/**
 * Builds JSON bodies for POST /v1/chat/completions (OpenAI-compatible schema).
 *
 * @see https://www.rodiumai.io/docs/api/chat-completions
 */
final class ChatPayloadBuilder
{
    public function __construct(
        private readonly string $defaultModel,
        private readonly ModelIdResolver $modelResolver,
        private readonly ?string $pendingModel = null,
        private readonly ?float $pendingTemperature = null,
        private readonly ?float $pendingTopP = null,
        private readonly ?int $pendingMaxTokens = null,
        private readonly ?string $pendingSystemPrompt = null,
    ) {}

    /**
     * @param  array<int, array{role: string, content: string}>|string  $messages
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function build(array|string $messages, array $options, bool $stream): array
    {
        if (is_string($messages)) {
            $messages = [['role' => 'user', 'content' => $messages]];
        }

        if ($this->pendingSystemPrompt !== null) {
            array_unshift($messages, ['role' => 'system', 'content' => $this->pendingSystemPrompt]);
        }

        $model = $options['model'] ?? $this->pendingModel ?? $this->defaultModel;

        $payload = [
            'model' => $this->modelResolver->resolve($model),
            'messages' => $messages,
            'stream' => $stream,
        ];

        $this->applyOptional($payload, 'temperature', $options['temperature'] ?? $this->pendingTemperature);
        $this->applyOptional($payload, 'top_p', $options['top_p'] ?? $this->pendingTopP);
        $this->applyOptional($payload, 'max_tokens', $options['max_tokens'] ?? $this->pendingMaxTokens);
        $this->applyOptional($payload, 'stop', $options['stop'] ?? null);

        return $payload;
    }

    private function applyOptional(array &$payload, string $key, mixed $value): void
    {
        if ($value !== null) {
            $payload[$key] = $value;
        }
    }
}
