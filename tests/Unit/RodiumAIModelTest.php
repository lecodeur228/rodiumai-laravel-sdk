<?php

namespace RodiumAI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RodiumAI\Enums\RodiumAIModality;
use RodiumAI\Enums\RodiumAIModel;
use RodiumAI\Enums\RodiumAIProvider;

class RodiumAIModelTest extends TestCase
{
    public function test_has_all_platform_models(): void
    {
        $this->assertGreaterThanOrEqual(45, count(RodiumAIModel::cases()));
    }

    public function test_gpt4o_value_and_provider(): void
    {
        $model = RodiumAIModel::OpenAiGpt4o;

        $this->assertSame('openai/gpt-4o', $model->value);
        $this->assertSame(RodiumAIProvider::OpenAi, $model->provider());
        $this->assertSame(RodiumAIModality::Text, $model->modality());
        $this->assertTrue($model->supportsChatCompletion());
    }

    public function test_image_model_modality(): void
    {
        $model = RodiumAIModel::OpenAiGptImage1;

        $this->assertSame(RodiumAIModality::Image, $model->modality());
        $this->assertFalse($model->supportsChatCompletion());
    }

    public function test_for_provider_filters(): void
    {
        $anthropic = RodiumAIModel::forProvider(RodiumAIProvider::Anthropic);

        $this->assertNotEmpty($anthropic);
        foreach ($anthropic as $model) {
            $this->assertSame(RodiumAIProvider::Anthropic, $model->provider());
        }
    }

    public function test_for_modality_filters(): void
    {
        $textModels = RodiumAIModel::forModality(RodiumAIModality::Text);

        $this->assertContains(RodiumAIModel::OpenAiGpt4o, $textModels);
        $this->assertNotContains(RodiumAIModel::GoogleVeo31GeneratePreview, $textModels);
    }

    public function test_try_from_api_id(): void
    {
        $this->assertSame(
            RodiumAIModel::DeepSeekDeepseekV4Flash,
            RodiumAIModel::tryFromApiId('deepseek/deepseek-v4-flash')
        );
        $this->assertNull(RodiumAIModel::tryFromApiId('unknown/provider-model'));
    }
}
