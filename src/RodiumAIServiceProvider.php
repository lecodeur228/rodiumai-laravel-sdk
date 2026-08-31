<?php

namespace RodiumAI;

use Illuminate\Support\ServiceProvider;

class RodiumAIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/rodiumai.php', 'rodiumai');

        $this->app->singleton(RodiumAIClient::class, function ($app) {
            $config = $app['config']['rodiumai'];

            return new RodiumAIClient(
                apiKey: $config['api_key'],
                timeout: $config['timeout'],
                defaultModel: $config['default_model'],
                baseUrl: $config['base_url'],
                locale: $config['locale'] ?? null,
            );
        });

        $this->app->alias(RodiumAIClient::class, 'rodiumai');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/rodiumai.php' => config_path('rodiumai.php'),
            ], 'rodiumai-config');
        }
    }
}
