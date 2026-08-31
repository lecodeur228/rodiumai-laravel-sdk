<?php

namespace RodiumAI\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use RodiumAI\Exceptions\ForbiddenException;
use RodiumAI\Exceptions\InsufficientCreditsException;
use RodiumAI\Exceptions\NotFoundException;
use RodiumAI\Exceptions\RateLimitException;
use RodiumAI\Exceptions\UnauthorizedException;
use RodiumAI\RodiumAIClient;
use RodiumAI\Support\ApiExceptionMapper;
use RodiumAI\Support\HttpTransport;
use RodiumAI\Support\RodiumAIMessages;

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

        $guzzle = new Client([
            'handler' => $stack,
            'base_uri' => 'https://api.rodiumai.io/v1/',
        ]);

        $transport = new HttpTransport(
            $guzzle,
            new ApiExceptionMapper(RodiumAIMessages::resolve('en')),
            'rdk_test',
        );

        return new RodiumAIClient(apiKey: 'rdk_test', transport: $transport);
    }

    public function test_chat_returns_chat_response_with_cost_rodi(): void
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
            'usage' => [
                'prompt_tokens' => 5,
                'completion_tokens' => 3,
                'total_tokens' => 8,
                'cost_rodi' => 0.0014,
            ],
            'rodiumai_routing' => [
                'requested' => 'rodiumai/smart',
                'resolved' => 'openai/gpt-4o',
            ],
        ]);

        $client = $this->makeClient([new Response(200, [], $fixture)]);
        $response = $client->chat('Bonjour');

        $this->assertSame('Bonjour !', $response->content);
        $this->assertSame(8, $response->totalTokens());
        $this->assertSame(0.0014, $response->costRodi());
        $this->assertSame('openai/gpt-4o', $response->routing()['resolved']);
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

    public function test_chat_passes_tools_option(): void
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
        $client->chat('Hi', ['tools' => [['type' => 'function', 'function' => ['name' => 'get_weather']]]]);

        $body = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertArrayHasKey('tools', $body);
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
        $client->model('anthropic/claude-sonnet-4-6')->chat('Hi');

        $body = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame('anthropic/claude-sonnet-4-6', $body['model']);
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

        $body = json_encode(['error' => ['message' => 'Invalid API key', 'code' => 'invalid_api_key']]);
        $client = $this->makeClient([new Response(401, [], $body)]);
        $client->chat('Test');
    }

    public function test_402_throws_insufficient_credits_exception(): void
    {
        $this->expectException(InsufficientCreditsException::class);

        $body = json_encode(['error' => ['message' => 'Insufficient RODI balance', 'code' => 'insufficient_balance']]);
        $client = $this->makeClient([new Response(402, [], $body)]);
        $client->chat('Test');
    }

    public function test_403_throws_forbidden_exception(): void
    {
        $this->expectException(ForbiddenException::class);

        $body = json_encode(['error' => ['message' => 'Model not allowed', 'code' => 'model_not_allowed']]);
        $client = $this->makeClient([new Response(403, [], $body)]);
        $client->chat('Test');
    }

    public function test_404_throws_not_found_exception(): void
    {
        $this->expectException(NotFoundException::class);

        $body = json_encode(['error' => ['message' => 'Model not found', 'code' => 'model_not_found']]);
        $client = $this->makeClient([new Response(404, [], $body)]);
        $client->modelInfo('missing/model');
    }

    public function test_429_throws_rate_limit_exception_with_retry_after(): void
    {
        $body = json_encode(['error' => ['message' => 'Rate limit exceeded', 'code' => 'rate_limit_exceeded']]);
        $client = $this->makeClient([new Response(429, ['Retry-After' => '30'], $body)]);

        try {
            $client->chat('Test');
            $this->fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            $this->assertSame(30, $e->retryAfter());
            $this->assertSame('rate_limit_exceeded', $e->errorCode());
        }
    }

    public function test_models_returns_model_collection_with_gateway_shape(): void
    {
        $fixture = json_encode([
            'object' => 'list',
            'data' => [
                [
                    'id' => 'openai/gpt-4o',
                    'object' => 'model',
                    'rodiumai_capabilities' => [
                        'context_window' => 128000,
                        'output_modalities' => ['text'],
                        'supports_tools' => true,
                    ],
                    'rodiumai_pricing' => ['currency' => 'RODI'],
                ],
                [
                    'id' => 'google/imagen-3',
                    'object' => 'model',
                    'rodiumai_capabilities' => [
                        'context_window' => 0,
                        'output_modalities' => ['image'],
                    ],
                ],
            ],
            'served_at' => 1746458400,
        ]);

        $client = $this->makeClient([new Response(200, [], $fixture)]);
        $models = $client->models();

        $this->assertSame(['openai/gpt-4o', 'google/imagen-3'], $models->ids());
        $this->assertCount(1, $models->chatModels());
        $this->assertSame(128000, $models->findById('openai/gpt-4o')?->contextWindow);
    }

    public function test_model_info_and_coding_models(): void
    {
        $modelFixture = json_encode([
            'id' => 'openai/gpt-4o',
            'rodiumai_capabilities' => ['context_window' => 128000, 'output_modalities' => ['text']],
        ]);
        $codingFixture = json_encode([
            'data' => [
                ['id' => 'openai/gpt-4o', 'rodiumai_capabilities' => ['output_modalities' => ['text']]],
            ],
        ]);

        $client = $this->makeClient([
            new Response(200, [], $modelFixture),
            new Response(200, [], $codingFixture),
        ]);

        $info = $client->modelInfo('openai/gpt-4o');
        $this->assertSame(128000, $info->contextWindow);

        $coding = $client->codingModels();
        $this->assertSame(['openai/gpt-4o'], $coding->ids());
    }

    public function test_embeddings(): void
    {
        $fixture = json_encode([
            'object' => 'list',
            'model' => 'openai/text-embedding-3-small',
            'data' => [['object' => 'embedding', 'embedding' => [0.1, 0.2], 'index' => 0]],
            'usage' => ['prompt_tokens' => 2, 'total_tokens' => 2],
        ]);

        $client = $this->makeClient([new Response(200, [], $fixture)]);
        $response = $client->embeddings('hello', ['model' => 'openai/text-embedding-3-small']);

        $this->assertSame([0.1, 0.2], $response->firstEmbedding());
    }

    public function test_wallet_and_pricing(): void
    {
        $walletFixture = json_encode([
            'object' => 'wallet',
            'balance_rodi' => '10.5',
            'reserved_rodi' => '0.0',
            'total_spent_rodi' => '5.0',
            'currency' => 'RODI',
        ]);
        $pricingFixture = json_encode([
            'object' => 'list',
            'currency' => 'RODI',
            'data' => [
                ['model' => 'openai/gpt-4o', 'pricing_unit' => 'per_million_tokens'],
            ],
        ]);

        $client = $this->makeClient([
            new Response(200, [], $walletFixture),
            new Response(200, [], $pricingFixture),
        ]);

        $wallet = $client->wallet();
        $this->assertSame('10.5', $wallet->balanceRodi);

        $pricing = $client->pricing();
        $this->assertSame('RODI', $pricing->currency());
        $this->assertSame('openai/gpt-4o', $pricing->findByModel('openai/gpt-4o')['model']);
    }

    public function test_messages_parses_anthropic_response(): void
    {
        $fixture = json_encode([
            'id' => 'msg_123',
            'type' => 'message',
            'role' => 'assistant',
            'model' => 'anthropic/claude-sonnet-4-6',
            'content' => [['type' => 'text', 'text' => 'Hello from Claude']],
            'stop_reason' => 'end_turn',
            'usage' => ['input_tokens' => 5, 'output_tokens' => 3],
        ]);

        $client = $this->makeClient([new Response(200, [], $fixture)]);
        $response = $client->messages([
            'model' => 'anthropic/claude-sonnet-4-6',
            'max_tokens' => 100,
            'messages' => [['role' => 'user', 'content' => 'Hi']],
        ]);

        $this->assertSame('Hello from Claude', $response->content);
    }

    public function test_anthropic_error_format_is_mapped(): void
    {
        $body = json_encode([
            'type' => 'error',
            'error' => ['type' => 'invalid_request_error', 'message' => 'Bad request'],
        ]);
        $client = $this->makeClient([new Response(422, [], $body)]);

        try {
            $client->messages(['model' => 'anthropic/claude-sonnet-4-6', 'max_tokens' => 1, 'messages' => []]);
            $this->fail('Expected exception');
        } catch (\RodiumAI\Exceptions\ValidationException $e) {
            $this->assertSame('Bad request', $e->getMessage());
            $this->assertSame('invalid_request_error', $e->errorCode());
        }
    }
}
