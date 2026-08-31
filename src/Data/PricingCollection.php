<?php

namespace RodiumAI\Data;

use Illuminate\Support\Collection;

/**
 * Parsed response from GET /v1/pricing.
 */
final class PricingCollection
{
    /** @var Collection<int, array<string, mixed>> */
    private Collection $items;

    private string $currency;

    /** @param list<array<string, mixed>> $items */
    public function __construct(array $items, string $currency = 'RODI')
    {
        $this->items = collect($items);
        $this->currency = $currency;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            items: $data['data'] ?? [],
            currency: (string) ($data['currency'] ?? 'RODI'),
        );
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->items->values()->all();
    }

    public function count(): int
    {
        return $this->items->count();
    }

    public function findByModel(string $model): ?array
    {
        return $this->items->first(fn (array $item) => ($item['model'] ?? null) === $model);
    }

    public function currency(): string
    {
        return $this->currency;
    }
}
