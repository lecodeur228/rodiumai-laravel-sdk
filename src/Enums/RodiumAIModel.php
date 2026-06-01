<?php

namespace RodiumAI\Enums;

/**
 * Modèles disponibles sur la plateforme RodiumAI (https://www.rodiumai.io/models).
 *
 * Généré automatiquement le 2026-06-01 via bin/generate-model-enum.php.
 * Régénérer lorsque de nouveaux modèles sont ajoutés à la plateforme.
 */
enum RodiumAIModel: string
{
    case AnthropicClaudeHaiku4520251001 = 'anthropic/claude-haiku-4-5-20251001';
    case AnthropicClaudeOpus4120250805 = 'anthropic/claude-opus-4-1-20250805';
    case AnthropicClaudeOpus4520251101 = 'anthropic/claude-opus-4-5-20251101';
    case AnthropicClaudeOpus46 = 'anthropic/claude-opus-4-6';
    case AnthropicClaudeOpus47 = 'anthropic/claude-opus-4-7';
    case AnthropicClaudeOpus48 = 'anthropic/claude-opus-4-8';
    case AnthropicClaudeSonnet4520250929 = 'anthropic/claude-sonnet-4-5-20250929';
    case AnthropicClaudeSonnet46 = 'anthropic/claude-sonnet-4-6';
    case DeepSeekDeepseekV4Flash = 'deepseek/deepseek-v4-flash';
    case DeepSeekDeepseekV4Pro = 'deepseek/deepseek-v4-pro';
    case GoogleGemini25Flash = 'google/gemini-2.5-flash';
    case GoogleGemini25FlashLite = 'google/gemini-2.5-flash-lite';
    case GoogleGemini25Pro = 'google/gemini-2.5-pro';
    case GoogleGemini31FlashLite = 'google/gemini-3.1-flash-lite';
    case GoogleGemini31FlashLitePreview = 'google/gemini-3.1-flash-lite-preview';
    case GoogleGemini31ProPreview = 'google/gemini-3.1-pro-preview';
    case GoogleGemini35Flash = 'google/gemini-3.5-flash';
    case GoogleImagen40FastGenerate001 = 'google/imagen-4.0-fast-generate-001';
    case GoogleImagen40Generate001 = 'google/imagen-4.0-generate-001';
    case GoogleImagen40UltraGenerate001 = 'google/imagen-4.0-ultra-generate-001';
    case GoogleTextEmbedding004 = 'google/text-embedding-004';
    case GoogleVeo31GeneratePreview = 'google/veo-3.1-generate-preview';
    case MiniMaxHailuo23 = 'minimax/hailuo-2-3';
    case MiniMaxMinimaxM25 = 'minimax/minimax-m2-5';
    case MiniMaxMinimaxM27 = 'minimax/minimax-m2-7';
    case OpenAiGpt41 = 'openai/gpt-4.1';
    case OpenAiGpt41Mini = 'openai/gpt-4.1-mini';
    case OpenAiGpt41Nano = 'openai/gpt-4.1-nano';
    case OpenAiGpt4o = 'openai/gpt-4o';
    case OpenAiGpt4oMini = 'openai/gpt-4o-mini';
    case OpenAiGpt5 = 'openai/gpt-5';
    case OpenAiGpt5Mini = 'openai/gpt-5-mini';
    case OpenAiGpt54 = 'openai/gpt-5.4';
    case OpenAiGpt54Mini = 'openai/gpt-5.4-mini';
    case OpenAiGpt54Nano = 'openai/gpt-5.4-nano';
    case OpenAiGpt54Pro = 'openai/gpt-5.4-pro';
    case OpenAiGpt55 = 'openai/gpt-5.5';
    case OpenAiGpt55Pro = 'openai/gpt-5.5-pro';
    case OpenAiGptImage1 = 'openai/gpt-image-1';
    case OpenAiGptImage1Mini = 'openai/gpt-image-1-mini';
    case OpenAiGptImage15 = 'openai/gpt-image-1.5';
    case OpenAiO3 = 'openai/o3';
    case OpenAiO3Mini = 'openai/o3-mini';
    case OpenAiO3Pro = 'openai/o3-pro';
    case OpenAiO4Mini = 'openai/o4-mini';

    public function provider(): RodiumAIProvider
    {
        return RodiumAIProvider::fromString(explode('/', $this->value, 2)[0]);
    }

    public function modality(): RodiumAIModality
    {
        return match ($this) {
            self::AnthropicClaudeHaiku4520251001 => RodiumAIModality::Text,
            self::AnthropicClaudeOpus4120250805 => RodiumAIModality::Text,
            self::AnthropicClaudeOpus4520251101 => RodiumAIModality::Text,
            self::AnthropicClaudeOpus46 => RodiumAIModality::Text,
            self::AnthropicClaudeOpus47 => RodiumAIModality::Text,
            self::AnthropicClaudeOpus48 => RodiumAIModality::Text,
            self::AnthropicClaudeSonnet4520250929 => RodiumAIModality::Text,
            self::AnthropicClaudeSonnet46 => RodiumAIModality::Text,
            self::DeepSeekDeepseekV4Flash => RodiumAIModality::Text,
            self::DeepSeekDeepseekV4Pro => RodiumAIModality::Text,
            self::GoogleGemini25Flash => RodiumAIModality::Text,
            self::GoogleGemini25FlashLite => RodiumAIModality::Text,
            self::GoogleGemini25Pro => RodiumAIModality::Text,
            self::GoogleGemini31FlashLite => RodiumAIModality::Text,
            self::GoogleGemini31FlashLitePreview => RodiumAIModality::Text,
            self::GoogleGemini31ProPreview => RodiumAIModality::Text,
            self::GoogleGemini35Flash => RodiumAIModality::Text,
            self::GoogleImagen40FastGenerate001 => RodiumAIModality::Image,
            self::GoogleImagen40Generate001 => RodiumAIModality::Image,
            self::GoogleImagen40UltraGenerate001 => RodiumAIModality::Image,
            self::GoogleTextEmbedding004 => RodiumAIModality::Embedding,
            self::GoogleVeo31GeneratePreview => RodiumAIModality::Video,
            self::MiniMaxHailuo23 => RodiumAIModality::Video,
            self::MiniMaxMinimaxM25 => RodiumAIModality::Text,
            self::MiniMaxMinimaxM27 => RodiumAIModality::Text,
            self::OpenAiGpt41 => RodiumAIModality::Text,
            self::OpenAiGpt41Mini => RodiumAIModality::Text,
            self::OpenAiGpt41Nano => RodiumAIModality::Text,
            self::OpenAiGpt4o => RodiumAIModality::Text,
            self::OpenAiGpt4oMini => RodiumAIModality::Text,
            self::OpenAiGpt5 => RodiumAIModality::Text,
            self::OpenAiGpt5Mini => RodiumAIModality::Text,
            self::OpenAiGpt54 => RodiumAIModality::Text,
            self::OpenAiGpt54Mini => RodiumAIModality::Text,
            self::OpenAiGpt54Nano => RodiumAIModality::Text,
            self::OpenAiGpt54Pro => RodiumAIModality::Text,
            self::OpenAiGpt55 => RodiumAIModality::Text,
            self::OpenAiGpt55Pro => RodiumAIModality::Text,
            self::OpenAiGptImage1 => RodiumAIModality::Image,
            self::OpenAiGptImage1Mini => RodiumAIModality::Image,
            self::OpenAiGptImage15 => RodiumAIModality::Image,
            self::OpenAiO3 => RodiumAIModality::Text,
            self::OpenAiO3Mini => RodiumAIModality::Text,
            self::OpenAiO3Pro => RodiumAIModality::Text,
            self::OpenAiO4Mini => RodiumAIModality::Text,
        };
    }

    /** Modèles utilisables avec {@see \RodiumAI\RodiumAIClient::chat()} / {@see stream()}. */
    public function supportsChatCompletion(): bool
    {
        return $this->modality() === RodiumAIModality::Text;
    }

    public static function tryFromApiId(string $id): ?self
    {
        return self::tryFrom($id);
    }

    /**
     * @return list<self>
     */
    public static function forProvider(RodiumAIProvider $provider): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $m) => $m->provider() === $provider
        ));
    }

    /**
     * @return list<self>
     */
    public static function forModality(RodiumAIModality $modality): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $m) => $m->modality() === $modality
        ));
    }
}
