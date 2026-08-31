<?php

namespace RodiumAI\Data;

/**
 * Parsed response from POST /v1/images/generations.
 *
 * @see https://www.rodiumai.io/docs/api/images
 */
final class ImageResponse
{
    /** @param list<array{b64_json?: string, url?: string}> $data */
    public function __construct(
        public readonly int $created,
        public readonly array $data,
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            created: (int) ($data['created'] ?? 0),
            data: $data['data'] ?? [],
            raw: $data,
        );
    }

    public function firstB64(): ?string
    {
        return $this->data[0]['b64_json'] ?? null;
    }

    public function firstUrl(): ?string
    {
        return $this->data[0]['url'] ?? null;
    }
}
