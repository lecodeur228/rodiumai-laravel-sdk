<?php

namespace RodiumAI\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use RodiumAI\Support\ChatPayloadBuilder;

class ChatPayloadBuilderTest extends TestCase
{
    public function test_builds_payload_with_top_p_and_model_string(): void
    {
        $builder = new ChatPayloadBuilder(
            defaultModel: 'openai/gpt-4o',
            pendingTopP: 0.8,
        );

        $payload = $builder->build(
            'Hello',
            [
                'model' => 'anthropic/claude-sonnet-4-6',
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

    public function test_passes_through_extra_options(): void
    {
        $builder = new ChatPayloadBuilder(defaultModel: 'openai/gpt-4o');

        $payload = $builder->build('Hi', [
            'tools' => [['type' => 'function']],
            'response_format' => ['type' => 'json_object'],
            'session_id' => 'abc',
        ], stream: false);

        $this->assertArrayHasKey('tools', $payload);
        $this->assertArrayHasKey('response_format', $payload);
        $this->assertSame('abc', $payload['session_id']);
    }
}
