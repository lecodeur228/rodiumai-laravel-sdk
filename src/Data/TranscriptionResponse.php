<?php

namespace RodiumAI\Data;

/**
 * Parsed response from POST /v1/audio/transcriptions.
 *
 * @see https://www.rodiumai.io/docs/api/transcriptions
 */
final class TranscriptionResponse
{
    public function __construct(
        public readonly string $text,
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'] ?? '',
            raw: $data,
        );
    }
}
