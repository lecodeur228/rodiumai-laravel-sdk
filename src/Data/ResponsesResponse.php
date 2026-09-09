<?php

namespace RodiumAI\Data;

/**
 * Parsed response from POST /v1/responses (OpenAI Responses API, non-streaming).
 *
 * @see https://www.rodiumai.io/docs/api/responses
 */
final class ResponsesResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $model,
        public readonly string $status,
        /** Convenience aggregation of every `output_text` block. */
        public readonly string $outputText,
        /** @var array<int, mixed> Raw `output` items. */
        public readonly array $output,
        /** @var array{input_tokens?: int, output_tokens?: int, total_tokens?: int} */
        public readonly array $usage,
        /** Full JSON body returned by the API. */
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        $output = is_array($data['output'] ?? null) ? $data['output'] : [];

        $outputText = $data['output_text'] ?? null;
        if (! is_string($outputText)) {
            $outputText = self::aggregateOutputText($output);
        }

        return new self(
            id: $data['id'] ?? '',
            model: $data['model'] ?? '',
            status: $data['status'] ?? '',
            outputText: $outputText,
            output: $output,
            usage: is_array($data['usage'] ?? null) ? $data['usage'] : [],
            raw: $data,
        );
    }

    /**
     * @param  array<int, mixed>  $output
     */
    private static function aggregateOutputText(array $output): string
    {
        $text = '';

        foreach ($output as $item) {
            $blocks = is_array($item) ? ($item['content'] ?? []) : [];

            if (! is_array($blocks)) {
                continue;
            }

            foreach ($blocks as $block) {
                if (is_array($block) && ($block['type'] ?? null) === 'output_text') {
                    $text .= $block['text'] ?? '';
                }
            }
        }

        return $text;
    }

    public function totalTokens(): int
    {
        return $this->usage['total_tokens'] ?? 0;
    }

    public function costRodi(): ?float
    {
        $cost = $this->raw['cost_rodi'] ?? $this->raw['rodiumai']['cost_rodi'] ?? null;

        return $cost === null ? null : (float) $cost;
    }
}
