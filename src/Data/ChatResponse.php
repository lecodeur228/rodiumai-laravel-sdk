<?php

namespace RodiumAI\Data;

/**
 * Parsed response from POST /v1/chat/completions (non-streaming).
 *
 * @see https://www.rodiumai.io/docs/api/chat-completions
 */
class ChatResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $model,
        public readonly string $content,
        public readonly string $finishReason,
        /** @var array{prompt_tokens?: int, completion_tokens?: int, total_tokens?: int} */
        public readonly array $usage,
        /** Full JSON body returned by the API. */
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        $choice = $data['choices'][0] ?? [];

        return new static(
            id: $data['id'] ?? '',
            model: $data['model'] ?? '',
            content: $choice['message']['content'] ?? '',
            finishReason: $choice['finish_reason'] ?? '',
            usage: $data['usage'] ?? [],
            raw: $data,
        );
    }

    public function totalTokens(): int
    {
        return $this->usage['total_tokens'] ?? 0;
    }
}
