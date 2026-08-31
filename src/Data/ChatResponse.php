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
        /** @var array{prompt_tokens?: int, completion_tokens?: int, total_tokens?: int, cost_rodi?: float|int|string} */
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

    public function costRodi(): ?float
    {
        if (! isset($this->usage['cost_rodi'])) {
            return null;
        }

        return (float) $this->usage['cost_rodi'];
    }

    /** @return array{requested?: string, resolved?: string, profile?: ?string}|null */
    public function routing(): ?array
    {
        $routing = $this->raw['rodiumai_routing'] ?? null;

        return is_array($routing) ? $routing : null;
    }
}
