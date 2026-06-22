<?php

namespace RodiumAI\Data;

use Illuminate\Support\Collection;

/**
 * Wrapper for GET /v1/models — dynamic model catalogue from the API.
 *
 * @see https://www.rodiumai.io/docs/api/models
 */
class ModelCollection
{
    /** @var Collection<int, ModelInfo> */
    private Collection $models;

    /** @param list<array<string, mixed>>|list<ModelInfo> $models */
    public function __construct(array $models)
    {
        $this->models = collect($models)->map(
            fn ($m) => $m instanceof ModelInfo ? $m : ModelInfo::fromArray($m)
        );
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

    public function count(): int
    {
        return $this->models->count();
    }

    /** @return list<ModelInfo> */
    public function all(): array
    {
        return $this->models->values()->all();
    }

    /** @return list<ModelInfo> */
    public function chatModels(): array
    {
        return $this->models
            ->filter(fn (ModelInfo $m) => $m->supportsChatCompletion())
            ->values()
            ->all();
    }

    public function findById(string $id): ?ModelInfo
    {
        return $this->models->first(fn (ModelInfo $m) => $m->id === $id);
    }

    public function byProvider(string $providerPrefix): static
    {
        return new static(
            $this->models
                ->filter(fn (ModelInfo $m) => $m->providerPrefix() === $providerPrefix)
                ->values()
                ->all()
        );
    }

    /** @return list<string> */
    public function providerPrefixes(): array
    {
        return $this->models
            ->map(fn (ModelInfo $m) => $m->providerPrefix())
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /** @return list<array<string, mixed>> */
    public function toArray(): array
    {
        return $this->models->map(fn (ModelInfo $m) => $m->raw)->all();
    }
}
