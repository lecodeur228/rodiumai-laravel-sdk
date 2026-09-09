<?php

namespace RodiumAI\Facades;

use Generator;
use Illuminate\Support\Facades\Facade;
use RodiumAI\Data\ChatResponse;
use RodiumAI\Data\EmbeddingResponse;
use RodiumAI\Data\ImageResponse;
use RodiumAI\Data\MessageResponse;
use RodiumAI\Data\ModelCollection;
use RodiumAI\Data\ModelInfo;
use RodiumAI\Data\PricingCollection;
use RodiumAI\Data\ResponsesResponse;
use RodiumAI\Data\TranscriptionResponse;
use RodiumAI\Data\VideoResponse;
use RodiumAI\Data\WalletResponse;
use RodiumAI\RodiumAIClient;

/**
 * @method static ChatResponse chat(array|string $messages, array $options = [])
 * @method static Generator stream(array|string $messages, array $options = [])
 * @method static ModelCollection models()
 * @method static ModelInfo modelInfo(string $id)
 * @method static ModelCollection codingModels()
 * @method static EmbeddingResponse embeddings(string|array $input, array $options = [])
 * @method static ImageResponse images(array $options)
 * @method static VideoResponse videos(array $options)
 * @method static TranscriptionResponse transcribe(string $filePath, array $options = [])
 * @method static string speech(array $options)
 * @method static MessageResponse messages(array $options)
 * @method static Generator messagesStream(array $options)
 * @method static ResponsesResponse responses(array $options)
 * @method static Generator responsesStream(array $options)
 * @method static WalletResponse wallet()
 * @method static PricingCollection pricing(?string $model = null)
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
