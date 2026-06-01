<?php

namespace RodiumAI\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use RodiumAI\Exceptions\InsufficientCreditsException;
use RodiumAI\Exceptions\RateLimitException;
use RodiumAI\Exceptions\UnauthorizedException;
use RodiumAI\Enums\RodiumAIModel;
use RodiumAI\RodiumAIClient;

class RodiumAIClientTest extends TestCase
{
    /**
     * @param  array<int, mixed>  $responses
     * @param  array<int, array<string, mixed>>|null  $history
     */
    private function makeClient(array $responses, ?array &$history = null): RodiumAIClient
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);

        if ($history !== null) {
            $history = [];
            $stack->push(Middleware::history($history));
        }

        $guzzle = new Client(['handler' => $stack]);

        $client = new RodiumAIClient(apiKey: 'rdk_test');
        $ref = new \ReflectionProperty(RodiumAIClient::class, 'http');
        $ref->setAccessible(true);
        $ref->setValue($client, $guzzle);

        return $client;
    }

    public function test_chat_returns_chat_response(): void
    {
        $fixture = json_encode([
            'id' => 'chatcmpl-123',
            'model' => 'openai/gpt-4o',
            'choices' => [
                [
                    'message' => ['role' => 'assistant', 'content' => 'Bonjour !'],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => ['prompt_tokens' => 5, 'completion_tokens' => 3, 'total_tokens' => 8],
        ]);

        $client = $this->makeClient([new Response(200, [], $fixture)]);
        $response = $client->chat('Bonjour');

        $this->assertSame('Bonjour !', $response->content);
        $this->assertSame(8, $response->totalTokens());
    }

    public function test_chat_with_string_message(): void
    {
        $history = [];
        $fixture = json_encode([
            'id' => 'chatcmpl-123',
            'model' => 'openai/gpt-4o',
            'choices' => [
                [
                    'message' => ['role' => 'assistant', 'content' => 'OK'],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => ['total_tokens' => 1],
        ]);

        $client = $this->makeClient([new Response(200, [], $fixture)], $history);
        $client->chat('Hello world');

        $body = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame([['role' => 'user', 'content' => 'Hello world']], $body['messages']);
    }

    public function test_fluent_builder_sets_model(): void
    {
        $history = [];
        $fixture = json_encode([
            'id' => 'chatcmpl-123',
            'model' => 'anthropic/claude-3-5-sonnet',
            'choices' => [
                [
                    'message' => ['role' => 'assistant', 'content' => 'Hi'],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => ['total_tokens' => 1],
        ]);

        $client = $this->makeClient([new Response(200, [], $fixture)], $history);
        $client->model(RodiumAIModel::AnthropicClaudeSonnet46)->chat('Hi');

        $body = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame('anthropic/claude-sonnet-4-6', $body['model']);
    }

    public function test_fluent_builder_prepends_system_prompt(): void
    {
        $history = [];
        $fixture = json_encode([
            'id' => 'chatcmpl-123',
            'model' => 'openai/gpt-4o',
            'choices' => [
                [
                    'message' => ['role' => 'assistant', 'content' => 'Hi'],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => ['total_tokens' => 1],
        ]);

        $client = $this->makeClient([new Response(200, [], $fixture)], $history);
        $client->systemPrompt('You are helpful.')->chat('Hi');

        $body = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame('system', $body['messages'][0]['role']);
        $this->assertSame('You are helpful.', $body['messages'][0]['content']);
        $this->assertSame('user', $body['messages'][1]['role']);
    }

    public function test_stream_yields_text_deltas(): void
    {
        $sse = implode("\n", [
            'data: {"choices":[{"delta":{"content":"Hello"}}]}',
            'data: {"choices":[{"delta":{"content":" world"}}]}',
            'data: [DONE]',
        ]) . "\n";

        $client = $this->makeClient([new Response(200, ['Content-Type' => 'text/event-stream'], $sse)]);

        $deltas = iterator_to_array($client->stream('Test'));

        $this->assertSame(['Hello', ' world'], $deltas);
    }

    public function test_401_throws_unauthorized_exception(): void
    {
        $this->expectException(UnauthorizedException::class);

        $body = json_encode(['error' => ['message' => 'Invalid API key']]);
        $client = $this->makeClient([new Response(401, [], $body)]);
        $client->chat('Test');
    }

    public function test_402_throws_insufficient_credits_exception(): void
    {
        $this->expectException(InsufficientCreditsException::class);

        $body = json_encode(['error' => ['message' => 'Insufficient RODI balance']]);
        $client = $this->makeClient([new Response(402, [], $body)]);
        $client->chat('Test');
    }

    public function test_429_throws_rate_limit_exception(): void
    {
        $this->expectException(RateLimitException::class);

        $body = json_encode(['error' => ['message' => 'Rate limit exceeded']]);
        $client = $this->makeClient([new Response(429, [], $body)]);
        $client->chat('Test');
    }

    public function test_model_accepts_enum(): void
    {
        $history = [];
        $fixture = json_encode([
            'id' => 'chatcmpl-123',
            'model' => 'openai/gpt-4o',
            'choices' => [
                [
                    'message' => ['role' => 'assistant', 'content' => 'Hi'],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => ['total_tokens' => 1],
        ]);

        $client = $this->makeClient([new Response(200, [], $fixture)], $history);
        $client->model(RodiumAIModel::OpenAiGpt4o)->chat('Hi');

        $body = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame('openai/gpt-4o', $body['model']);
    }

    public function test_top_p_fluent_builder(): void
    {
        $history = [];
        $fixture = json_encode([
            'id' => 'chatcmpl-123',
            'model' => 'openai/gpt-4o',
            'choices' => [
                [
                    'message' => ['role' => 'assistant', 'content' => 'Hi'],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => ['total_tokens' => 1],
        ]);

        $client = $this->makeClient([new Response(200, [], $fixture)], $history);
        $client->topP(0.9)->chat('Hi');

        $body = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame(0.9, $body['top_p']);
    }

    public function test_401_exception_includes_response_body(): void
    {
        $body = json_encode(['error' => ['message' => 'Invalid API key', 'type' => 'invalid_request_error']]);
        $client = $this->makeClient([new Response(401, [], $body)]);

        try {
            $client->chat('Test');
            $this->fail('Expected UnauthorizedException');
        } catch (UnauthorizedException $e) {
            $this->assertSame('Invalid API key', $e->getMessage());
            $this->assertIsArray($e->responseBody());
            $this->assertSame('Invalid API key', $e->responseBody()['error']['message']);
        }
    }

    public function test_invalid_model_string_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->makeClient([]);
        $client->model('invalid/unknown-model')->chat('Hi');
    }

    public function test_models_returns_model_collection(): void
    {
        $fixture = json_encode([
            'data' => [
                ['id' => 'openai/gpt-4o', 'context_window' => 128000],
                ['id' => 'anthropic/claude-3-5-sonnet', 'context_window' => 200000],
            ],
        ]);

        $client = $this->makeClient([new Response(200, [], $fixture)]);
        $models = $client->models();

        $this->assertSame(['openai/gpt-4o', 'anthropic/claude-3-5-sonnet'], $models->ids());
    }
}
