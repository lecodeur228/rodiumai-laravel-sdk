# rodiumai/laravel-sdk

Official PHP / Laravel SDK for the [Rodium AI](https://www.rodiumai.io) API — unified access to AI models (OpenAI, Anthropic, Google, DeepSeek…) with **RODI** credit billing and **Mobile Money** top-ups.

> **OpenAI-compatible** REST API: same endpoints and payloads as documented at [rodiumai.io/docs](https://www.rodiumai.io/docs).

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rodiumai/laravel-sdk.svg)](https://packagist.org/packages/rodiumai/laravel-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/rodiumai/laravel-sdk.svg)](https://packagist.org/packages/rodiumai/laravel-sdk)
[![PHP Version](https://img.shields.io/packagist/php-v/rodiumai/laravel-sdk.svg)](https://packagist.org/packages/rodiumai/laravel-sdk)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Tests](https://github.com/lecodeur228/rodiumai-laravel-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/lecodeur228/rodiumai-laravel-sdk/actions)

## Links

| Resource | URL |
|----------|-----|
| **Packagist** | [packagist.org/packages/rodiumai/laravel-sdk](https://packagist.org/packages/rodiumai/laravel-sdk) |
| **Source code** | [github.com/lecodeur228/rodiumai-laravel-sdk](https://github.com/lecodeur228/rodiumai-laravel-sdk) |
| **Laravel SDK guide** | [rodiumai.io/docs/guides/laravel-sdk](https://www.rodiumai.io/docs/guides/laravel-sdk) |
| **API documentation** | [rodiumai.io/docs](https://www.rodiumai.io/docs) |
| **Dashboard & API keys** | [rodiumai.io/dashboard](https://www.rodiumai.io/dashboard) |

## Table of contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick start](#quick-start)
- [Chat completions](#chat-completions)
- [Streaming (SSE)](#streaming-sse)
- [Models](#models)
- [Embeddings](#embeddings)
- [Images & videos](#images--videos)
- [Audio](#audio)
- [Anthropic Messages](#anthropic-messages)
- [Wallet & pricing](#wallet--pricing)
- [Error handling](#error-handling)
- [SDK reference](#sdk-reference)
- [Local development](#local-development)
- [Migration 0.1.x → 0.2.0](#migration-01x--020)
- [Testing](#testing)
- [License](#license)

## Requirements

- PHP **8.1+** with the `json` extension
- Laravel **10–13** (optional — `RodiumAIClient` works in plain PHP)
- Rodium AI account + API key (`rd_sk_…`): [dashboard](https://www.rodiumai.io/dashboard)

## Installation

```bash
composer require rodiumai/laravel-sdk:^0.2
```

Publish config (optional):

```bash
php artisan vendor:publish --tag=rodiumai-config
```

The `ServiceProvider` and `RodiumAI` Facade are **auto-discovered**.

## Configuration

`.env`:

```env
RODIUMAI_API_KEY=rd_sk_your_secret_key
RODIUMAI_BASE_URL=https://api.rodiumai.io/v1
RODIUMAI_DEFAULT_MODEL=openai/gpt-4o
RODIUMAI_TIMEOUT=30
# RODIUMAI_LOCALE=fr   # optional — SDK error hints (en, fr, es)
```

| Variable | Default | Description |
|----------|---------|-------------|
| `RODIUMAI_API_KEY` | — | Secret key from the dashboard |
| `RODIUMAI_BASE_URL` | `https://api.rodiumai.io/v1` | Gateway base URL (use `http://localhost:8001/v1` locally) |
| `RODIUMAI_DEFAULT_MODEL` | `openai/gpt-4o` | Default model slug |
| `RODIUMAI_TIMEOUT` | `30` | HTTP timeout in seconds |
| `RODIUMAI_LOCALE` | `en` | Localized exception hints |

Never commit `.env` or API keys.

## Quick start

### Laravel (Facade)

```php
use RodiumAI\Facades\RodiumAI;

$response = RodiumAI::model('openai/gpt-4o')
    ->temperature(0.7)
    ->maxTokens(300)
    ->chat('Explain Rodium AI in two sentences.');

echo $response->content;
echo $response->costRodi(); // RODI cost from usage.cost_rodi
```

### Plain PHP

```php
use RodiumAI\RodiumAIClient;

$client = new RodiumAIClient(apiKey: getenv('RODIUMAI_API_KEY'));
$response = $client->chat('Hello!');
echo $response->content;
```

## Chat completions

Aligned with [docs/api/chat-completions](https://www.rodiumai.io/docs/api/chat-completions).

```php
use RodiumAI\Data\ChatMessage;

$messages = [
    ChatMessage::system('You are a Laravel assistant.'),
    ChatMessage::user('What is a Service Provider?'),
];

$response = RodiumAI::model('openai/gpt-4o')
    ->temperature(0.5)
    ->maxTokens(500)
    ->chat($messages, [
        'tools' => [/* OpenAI tool definitions */],
        'response_format' => ['type' => 'json_object'],
    ]);
```

### Smart routing

Use `rodiumai/smart` as the model id — the resolved model and routing metadata are returned:

```php
$response = RodiumAI::model('rodiumai/smart')->chat('Summarize RODI credits.');
$routing = $response->routing(); // ['requested' => 'rodiumai/smart', 'resolved' => 'openai/gpt-4o', …]
```

See [Smart routing guide](https://www.rodiumai.io/docs/guides/smart).

## Streaming (SSE)

```php
foreach (RodiumAI::model('openai/gpt-4o')->stream('Tell a short story.') as $delta) {
    echo $delta;
}
```

Laravel `StreamedResponse` example:

```php
return response()->stream(function () {
    foreach (RodiumAI::stream('Hello') as $delta) {
        echo 'data: ' . json_encode(['delta' => $delta]) . "\n\n";
        ob_flush();
        flush();
    }
    echo "data: [DONE]\n\n";
}, 200, ['Content-Type' => 'text/event-stream', 'Cache-Control' => 'no-cache']);
```

## Models

```php
$catalogue = RodiumAI::models();

$catalogue->ids();
$catalogue->chatModels();
$catalogue->byProvider('anthropic');
$catalogue->findById('openai/gpt-4o');

$info = RodiumAI::modelInfo('openai/gpt-4o');
$info->contextWindow;   // from rodiumai_capabilities
$info->pricing();       // RODI rates

$coding = RodiumAI::codingModels(); // GET /v1/models/coding
```

## Embeddings

```php
$response = RodiumAI::embeddings('Hello world', [
    'model' => 'openai/text-embedding-3-small',
]);

$vector = $response->firstEmbedding();
```

## Images & videos

```php
$image = RodiumAI::images([
    'model' => 'openai/gpt-image-1',
    'prompt' => 'A sunset over Lomé',
    'size' => '1024x1024',
]);

$b64 = $image->firstB64();

$video = RodiumAI::videos([
    'model' => 'google/veo-3.1-generate-preview',
    'prompt' => 'Ocean waves at golden hour',
    'duration_seconds' => 8,
    'timeout' => 600,
]);
```

## Audio

```php
// Transcription (multipart upload)
$transcript = RodiumAI::transcribe('/path/to/audio.mp3', [
    'model' => 'google/gemini-2.5-flash',
    'language' => 'fr',
]);
echo $transcript->text;

// Text-to-speech (returns raw audio bytes)
$audioBytes = RodiumAI::speech([
    'model' => 'openai/tts-1',
    'input' => 'Hello from RodiumAI',
    'voice' => 'alloy',
]);
file_put_contents('speech.mp3', $audioBytes);
```

## Anthropic Messages

Drop-in for [POST /v1/messages](https://www.rodiumai.io/docs/api/messages):

```php
$response = RodiumAI::messages([
    'model' => 'anthropic/claude-sonnet-4-6',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Explain RODI credits.'],
    ],
]);

echo $response->content;
```

## Wallet & pricing

RodiumAI extensions:

```php
$wallet = RodiumAI::wallet();
echo $wallet->balanceRodi;

$pricing = RodiumAI::pricing();
$pricing->findByModel('openai/gpt-4o');
```

## Error handling

See [docs/api/errors](https://www.rodiumai.io/docs/api/errors).

| HTTP | Exception | Notes |
|------|-----------|-------|
| 401 | `UnauthorizedException` | Invalid or missing API key |
| 402 | `InsufficientCreditsException` | Top up RODI in dashboard |
| 403 | `ForbiddenException` | Scope or model whitelist |
| 404 | `NotFoundException` | Unknown model or resource |
| 422 | `ValidationException` | Invalid request body |
| 429 | `RateLimitException` | Use `$e->retryAfter()` for backoff |
| Other | `RodiumAIException` | `$e->errorCode()`, `$e->responseBody()` |

```php
use RodiumAI\Exceptions\InsufficientCreditsException;
use RodiumAI\Exceptions\RateLimitException;
use RodiumAI\Facades\RodiumAI;

try {
    RodiumAI::chat('Test');
} catch (InsufficientCreditsException $e) {
    logger()->warning('Insufficient RODI', ['code' => $e->errorCode()]);
} catch (RateLimitException $e) {
    sleep($e->retryAfter() ?? 30);
}
```

## SDK reference

| Method | Returns | Gateway route |
|--------|---------|---------------|
| `chat($messages, $options)` | `ChatResponse` | `POST /v1/chat/completions` |
| `stream($messages, $options)` | `Generator<string>` | `POST /v1/chat/completions` (SSE) |
| `models()` | `ModelCollection` | `GET /v1/models` |
| `modelInfo($id)` | `ModelInfo` | `GET /v1/models/{id}` |
| `codingModels()` | `ModelCollection` | `GET /v1/models/coding` |
| `embeddings($input, $options)` | `EmbeddingResponse` | `POST /v1/embeddings` |
| `images($options)` | `ImageResponse` | `POST /v1/images/generations` |
| `videos($options)` | `VideoResponse` | `POST /v1/videos/generations` |
| `transcribe($filePath, $options)` | `TranscriptionResponse` | `POST /v1/audio/transcriptions` |
| `speech($options)` | `string` (bytes) | `POST /v1/audio/speech` |
| `messages($options)` | `MessageResponse` | `POST /v1/messages` |
| `wallet()` | `WalletResponse` | `GET /v1/wallet` |
| `pricing($model?)` | `PricingCollection` | `GET /v1/pricing` |

Fluent builder: `model()`, `temperature()`, `topP()`, `maxTokens()`, `systemPrompt()`, `language()`.

DTOs: `ChatResponse`, `ChatMessage`, `ModelInfo`, `ModelCollection`, `EmbeddingResponse`, `ImageResponse`, `VideoResponse`, `TranscriptionResponse`, `MessageResponse`, `WalletResponse`, `PricingCollection`.

Technical mapping: [docs/api-alignment.md](docs/api-alignment.md).

## Local development

Point the SDK at a local gateway (e.g. Docker Compose on port 8001):

```env
RODIUMAI_BASE_URL=http://localhost:8001/v1
RODIUMAI_API_KEY=rd_sk_dev_...
```

## Migration 0.1.x → 0.2.0

| Change | Action |
|--------|--------|
| `base_url` restored | Set `RODIUMAI_BASE_URL` if not using production |
| Static enums removed | Use `models()` / `ModelCollection` for catalogue |
| `ModelInfo::contextWindow` | Now reads `rodiumai_capabilities.context_window` (gateway shape) |
| New methods | `embeddings`, `images`, `videos`, `transcribe`, `speech`, `messages`, `wallet`, `pricing` |

Require `^0.2` in `composer.json`.

## Testing

```bash
composer install
composer test                 # PHPUnit (mocked HTTP)
export RODIUMAI_API_KEY="rd_sk_..."
php bin/smoke-test.php        # Live API walkthrough
```

## License

MIT — see [LICENSE](LICENSE).
