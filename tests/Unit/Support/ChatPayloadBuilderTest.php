<?php

namespace RodiumAI\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use RodiumAI\Enums\RodiumAIModel;
use RodiumAI\Support\ChatPayloadBuilder;
use RodiumAI\Support\ModelIdResolver;

class ChatPayloadBuilderTest extends TestCase
{
    public function test_builds_payload_with_top_p_and_enum_model(): void
    {
        $builder = new ChatPayloadBuilder(
            defaultModel: 'openai/gpt-4o',
            modelResolver: new ModelIdResolver,
            pendingTopP: 0.8,
        );

        $payload = $builder->build(
            'Hello',
            [
                'model' => RodiumAIModel::AnthropicClaudeSonnet46,
                'top_p' => 0.5,
                'stop' => ['END'],
            ],
            stream: false,
        );

        $this->assertSame('anthropic/claude-sonnet-4-6', $payload['model']);
        $this->assertFalse($payload['stream']);
        $this->assertSame(0.5, $payload['top_p']);
        $this->assertSame(['END'], $payload['stop']);
    }
}
