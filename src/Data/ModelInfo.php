<?php

namespace RodiumAI\Data;

/**
 * Model metadata from GET /v1/models (dynamic catalogue).
 *
 * @see https://www.rodiumai.io/docs/api/models
 */
final class ModelInfo
{
    /** @param list<string> $outputModalities */
    public function __construct(
        public readonly string $id,
        public readonly ?int $contextWindow,
        public readonly array $outputModalities,
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        $capabilities = $data['rodiumai_capabilities'] ?? [];
        $modalities = $capabilities['output_modalities'] ?? [];

        return new self(
            id: $data['id'],
            contextWindow: isset($capabilities['context_window'])
                ? (int) $capabilities['context_window']
                : (isset($data['context_window']) ? (int) $data['context_window'] : null),
            outputModalities: is_array($modalities) ? array_values(array_filter($modalities, 'is_string')) : [],
            raw: $data,
        );
    }

    public function providerPrefix(): string
    {
        $parts = explode('/', $this->id, 2);

        return $parts[0];
    }

    /** @return array{slug?: string, name?: string}|null */
    public function provider(): ?array
    {
        $provider = $this->raw['rodiumai_provider'] ?? null;

        return is_array($provider) ? $provider : null;
    }

    public function displayName(): ?string
    {
        return $this->raw['rodiumai_display_name'] ?? null;
    }

    /** @return array<string, mixed>|null */
    public function pricing(): ?array
    {
        $pricing = $this->raw['rodiumai_pricing'] ?? null;

        return is_array($pricing) ? $pricing : null;
    }

    public function status(): ?string
    {
        return $this->raw['rodiumai_status'] ?? null;
    }

    public function supportsChatCompletion(): bool
    {
        if ($this->outputModalities !== []) {
            return in_array('text', $this->outputModalities, true);
        }

        return $this->contextWindow !== null && $this->contextWindow > 0;
    }

    public function supportsTools(): bool
    {
        return (bool) ($this->raw['rodiumai_capabilities']['supports_tools'] ?? false);
    }

    public function supportsVision(): bool
    {
        return (bool) ($this->raw['rodiumai_capabilities']['supports_vision'] ?? false);
    }
}
