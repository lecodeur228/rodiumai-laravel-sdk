<?php

namespace RodiumAI\Data;

/**
 * Parsed response from POST /v1/embeddings.
 *
 * @see https://www.rodiumai.io/docs/api/embeddings
 */
final class EmbeddingResponse
{
    /** @param list<array{object?: string, embedding?: list<float>, index?: int}> $data */
    public function __construct(
        public readonly string $model,
        public readonly array $data,
        public readonly array $usage,
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            model: $data['model'] ?? '',
            data: $data['data'] ?? [],
            usage: $data['usage'] ?? [],
            raw: $data,
        );
    }

    /** @return list<float> */
    public function firstEmbedding(): array
    {
        return $this->data[0]['embedding'] ?? [];
    }
}
