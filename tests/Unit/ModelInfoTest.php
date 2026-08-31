<?php

namespace RodiumAI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RodiumAI\Data\ModelInfo;

class ModelInfoTest extends TestCase
{
    public function test_parses_gateway_capabilities_shape(): void
    {
        $info = ModelInfo::fromArray([
            'id' => 'openai/gpt-4o',
            'rodiumai_provider' => ['slug' => 'openai', 'name' => 'OpenAI'],
            'rodiumai_display_name' => 'GPT-4o',
            'rodiumai_pricing' => [
                'pricing_unit' => 'per_million_tokens',
                'input_per_1m' => '12.5',
                'currency' => 'RODI',
            ],
            'rodiumai_capabilities' => [
                'context_window' => 128000,
                'output_modalities' => ['text'],
                'supports_tools' => true,
                'supports_vision' => true,
            ],
            'rodiumai_status' => 'available',
        ]);

        $this->assertSame(128000, $info->contextWindow);
        $this->assertSame(['text'], $info->outputModalities);
        $this->assertTrue($info->supportsChatCompletion());
        $this->assertTrue($info->supportsTools());
        $this->assertTrue($info->supportsVision());
        $this->assertSame('openai', $info->providerPrefix());
        $this->assertSame('GPT-4o', $info->displayName());
        $this->assertSame('available', $info->status());
        $this->assertSame('RODI', $info->pricing()['currency']);
    }
}
