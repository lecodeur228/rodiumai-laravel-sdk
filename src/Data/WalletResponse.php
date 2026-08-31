<?php

namespace RodiumAI\Data;

/**
 * Parsed response from GET /v1/wallet.
 *
 * @see https://www.rodiumai.io/docs/api/overview
 */
final class WalletResponse
{
    public function __construct(
        public readonly string $balanceRodi,
        public readonly string $reservedRodi,
        public readonly string $totalSpentRodi,
        public readonly string $currency,
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            balanceRodi: (string) ($data['balance_rodi'] ?? '0'),
            reservedRodi: (string) ($data['reserved_rodi'] ?? '0'),
            totalSpentRodi: (string) ($data['total_spent_rodi'] ?? '0'),
            currency: (string) ($data['currency'] ?? 'RODI'),
            raw: $data,
        );
    }
}
