<?php

namespace RodiumAI\Data;

/**
 * Parsed response from POST /v1/messages (Anthropic-compatible).
 *
 * @see https://www.rodiumai.io/docs/api/messages
 */
final class MessageResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $model,
        public readonly string $content,
        public readonly string $stopReason,
        public readonly array $usage,
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        $content = '';
        $blocks = $data['content'] ?? [];

        if (is_array($blocks)) {
            foreach ($blocks as $block) {
                if (is_array($block) && ($block['type'] ?? null) === 'text') {
                    $content .= $block['text'] ?? '';
                }
            }
        }

        return new self(
            id: $data['id'] ?? '',
            model: $data['model'] ?? '',
            content: $content,
            stopReason: $data['stop_reason'] ?? '',
            usage: $data['usage'] ?? [],
            raw: $data,
        );
    }
}
