<?php

namespace RodiumAI\Tests\Feature;

use Orchestra\Testbench\TestCase;
use RodiumAI\Facades\RodiumAI;
use RodiumAI\RodiumAIClient;
use RodiumAI\RodiumAIServiceProvider;

class FacadeTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [RodiumAIServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'RodiumAI' => RodiumAI::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('rodiumai', [
            'api_key' => 'rdk_test',
            'base_url' => 'https://api.rodiumai.io/v1',
            'default_model' => 'openai/gpt-4o',
            'timeout' => 30,
        ]);
    }

    public function test_facade_resolves_client_from_container(): void
    {
        $client = RodiumAI::getFacadeRoot();

        $this->assertInstanceOf(RodiumAIClient::class, $client);
        $this->assertSame($client, $this->app->make('rodiumai'));
        $this->assertSame($client, $this->app->make(RodiumAIClient::class));
    }
}
