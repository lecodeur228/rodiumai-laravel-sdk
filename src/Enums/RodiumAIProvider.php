<?php

namespace RodiumAI\Enums;

enum RodiumAIProvider: string
{
    case Anthropic = 'anthropic';
    case DeepSeek = 'deepseek';
    case Google = 'google';
    case MiniMax = 'minimax';
    case OpenAi = 'openai';

    public static function fromString(string $slug): self
    {
        return self::tryFrom($slug)
            ?? throw new \InvalidArgumentException("Unknown RodiumAI provider: {$slug}");
    }

    public function label(): string
    {
        return match ($this) {
            self::Anthropic => 'Anthropic',
            self::DeepSeek => 'DeepSeek',
            self::Google => 'Google',
            self::MiniMax => 'MiniMax',
            self::OpenAi => 'OpenAI',
        };
    }
}
