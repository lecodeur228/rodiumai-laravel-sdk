<?php

namespace RodiumAI\Facades;

use Generator;
use Illuminate\Support\Facades\Facade;
use RodiumAI\Data\ChatResponse;
use RodiumAI\Data\ModelCollection;
use RodiumAI\RodiumAIClient;

/**
 * @method static ChatResponse chat(array|string $messages, array $options = [])
 * @method static Generator stream(array|string $messages, array $options = [])
 * @method static ModelCollection models()
 * @method static RodiumAIClient model(string $model)
 * @method static RodiumAIClient temperature(float $temperature)
 * @method static RodiumAIClient topP(float $topP)
 * @method static RodiumAIClient maxTokens(int $maxTokens)
 * @method static RodiumAIClient systemPrompt(string $prompt)
 * @method static RodiumAIClient language(string $locale)
 *
 * @see RodiumAIClient
 */
class RodiumAI extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'rodiumai';
    }
}
