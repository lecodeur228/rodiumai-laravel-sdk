<?php

namespace RodiumAI\Data;

use Illuminate\Support\Collection;
use RodiumAI\Enums\RodiumAIProvider;

/**
 * Wrapper for GET /v1/models response data.
 *
 * @see https://www.rodiumai.io/docs/api/models
 */
class ModelCollection
{
    private Collection $models;

    public function __construct(array $models)
    {
        $this->models = collect($models);
    }

    public static function fromArray(array $data): static
    {
        return new static($data['data'] ?? []);
    }

    /** @return list<string> */
    public function ids(): array
    {
        return $this->models->pluck('id')->values()->all();
    }

    public function byProvider(string|RodiumAIProvider $provider): static
    {
        $prefix = ($provider instanceof RodiumAIProvider ? $provider : RodiumAIProvider::fromString($provider))->value;

        return new static(
            $this->models
                ->filter(fn (array $m) => str_starts_with($m['id'], $prefix . '/'))
                ->values()
                ->all()
        );
    }

    public function toArray(): array
    {
        return $this->models->all();
    }

    public function count(): int
    {
        return $this->models->count();
    }
}
