# rodiumai/laravel-sdk

Official PHP / Laravel SDK for the [Rodium AI](https://www.rodiumai.io) API — unified access to AI models (OpenAI, Anthropic, Google, DeepSeek, MiniMax…) with **RODI** credit billing and **Mobile Money** top-ups.

> **OpenAI-compatible** REST API: same endpoints and payloads as documented at [rodiumai.io/docs](https://www.rodiumai.io/docs).

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rodiumai/laravel-sdk.svg)](https://packagist.org/packages/rodiumai/laravel-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/rodiumai/laravel-sdk.svg)](https://packagist.org/packages/rodiumai/laravel-sdk)
[![PHP Version](https://img.shields.io/packagist/php-v/rodiumai/laravel-sdk.svg)](https://packagist.org/packages/rodiumai/laravel-sdk)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Tests](https://github.com/lecodeur228/rodiumai-laravel-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/lecodeur228/rodiumai-laravel-sdk/actions)

## Links

| Resource | URL |
|----------|-----|
| **Packagist** (Composer install) | [packagist.org/packages/rodiumai/laravel-sdk](https://packagist.org/packages/rodiumai/laravel-sdk) |
| **Source code** | [github.com/lecodeur228/rodiumai-laravel-sdk](https://github.com/lecodeur228/rodiumai-laravel-sdk) |
| **API documentation** | [rodiumai.io/docs](https://www.rodiumai.io/docs) |
| **Dashboard & API keys** | [rodiumai.io/dashboard](https://www.rodiumai.io/dashboard) |
| **Model catalogue** | [rodiumai.io/models](https://www.rodiumai.io/models) |

## Table of contents

- [Official documentation](#official-documentation)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick start](#quick-start)
- [Typed models (enum)](#typed-models-enum)
- [Chat parameters](#chat-parameters)
- [Streaming (SSE)](#streaming-sse)
- [Listing models](#listing-models)
- [Error handling](#error-handling)
- [SDK reference](#sdk-reference)
- [Testing & development](#testing--development)
- [Contributing](#contributing)
- [License](#license)

## Official documentation

| Topic | Rodium AI link |
|-------|----------------|
| Quickstart | [rodiumai.io/docs](https://www.rodiumai.io/docs) |
| API overview | [docs/api/overview](https://www.rodiumai.io/docs/api/overview) |
| Chat completions | [docs/api/chat-completions](https://www.rodiumai.io/docs/api/chat-completions) |
| Streaming SSE | [docs/api/streaming](https://www.rodiumai.io/docs/api/streaming) |
| Models | [docs/api/models](https://www.rodiumai.io/docs/api/models) · [Catalogue](https://www.rodiumai.io/models) |
| HTTP errors | [docs/api/errors](https://www.rodiumai.io/docs/api/errors) |

Detailed SDK ↔ API mapping: [docs/api-alignment.md](docs/api-alignment.md).

## Requirements

- PHP **8.1+** with the `json` extension
- Laravel **10**, **11**, or **12** (optional — the client works in plain PHP)
- Laravel **12**: PHP **8.2+** → use **`^0.1.1`** minimum
- Rodium AI account + API key: [dashboard](https://www.rodiumai.io/dashboard)

## Installation

Install from **[Packagist](https://packagist.org/packages/rodiumai/laravel-sdk)**:

```bash
composer require rodiumai/laravel-sdk
```

For **Laravel 12** (PHP 8.2+):

```bash
composer require rodiumai/laravel-sdk:^0.1.1
```

### After installation

1. Publish config (optional but recommended):

```bash
php artisan vendor:publish --tag=rodiumai-config
```

2. Add your API key to `.env` (see [Configuration](#configuration)).

3. The `ServiceProvider` and `RodiumAI` Facade are **auto-discovered** — nothing to register in `bootstrap/providers.php`.

### Plain PHP (no Laravel)

Same Composer command. Then instantiate `RodiumAI\RodiumAIClient` directly (see [Quick start](#quick-start)).

## Configuration

`.env` file:

```env
RODIUMAI_API_KEY=rd_sk_your_secret_key
RODIUMAI_BASE_URL=https://api.rodiumai.io/v1
RODIUMAI_DEFAULT_MODEL=openai/gpt-4o
RODIUMAI_TIMEOUT=30
```

Never commit `.env` or API keys to the repository.

## Quick start

### Laravel (Facade)

```php
use RodiumAI\Enums\RodiumAIModel;
use RodiumAI\Facades\RodiumAI;

$response = RodiumAI::model(RodiumAIModel::OpenAiGpt4o)
    ->temperature(0.7)
    ->maxTokens(300)
    ->chat('Explain Rodium AI in two sentences.');

echo $response->content;
echo $response->totalTokens(); // billed tokens
```

### Plain PHP

```php
use RodiumAI\Enums\RodiumAIModel;
use RodiumAI\RodiumAIClient;

$client = new RodiumAIClient(
    apiKey: getenv('RODIUMAI_API_KEY'),
);

$response = $client
    ->model(RodiumAIModel::AnthropicClaudeSonnet46)
    ->chat('Hello!');

echo $response->content;
```

Equivalent to the [cURL / OpenAI SDK quickstart](https://www.rodiumai.io/docs): `POST https://api.rodiumai.io/v1/chat/completions` with `Authorization: Bearer {RODIUMAI_API_KEY}`.

## Typed models (enum)

**45+** catalogue models are available via `RodiumAIModel` — IDE autocomplete and runtime validation:

```php
use RodiumAI\Enums\RodiumAIModality;
use RodiumAI\Enums\RodiumAIModel;
use RodiumAI\Enums\RodiumAIProvider;

RodiumAI::model(RodiumAIModel::OpenAiGpt4o)->chat('…');

// Filters
RodiumAIModel::forProvider(RodiumAIProvider::Google);
RodiumAIModel::forModality(RodiumAIModality::Text);

// Text chat only
RodiumAIModel::OpenAiGpt4o->supportsChatCompletion(); // true
```

When Rodium AI adds new models:

```bash
RODIUMAI_API_KEY="…" php bin/generate-model-enum.php
```

## Chat parameters

Aligned with [chat-completions](https://www.rodiumai.io/docs/api/chat-completions):

| API parameter | SDK |
|---------------|-----|
| `model` | `->model()` / `RodiumAIModel` / `$options['model']` |
| `messages` | Array of `{role, content}` or `string` (→ `user` message) |
| `max_tokens` | `->maxTokens()` / `$options['max_tokens']` |
| `temperature` (0–2) | `->temperature()` / `$options['temperature']` |
| `top_p` (0–1) | `->topP()` / `$options['top_p']` |
| `stop` | `$options['stop']` |
| `stream` | Set automatically by `->stream()` |

```php
use RodiumAI\Data\ChatMessage;

$messages = [
    ChatMessage::system('You are a Laravel assistant.'),
    ChatMessage::user('What is a Service Provider?'),
];

$response = RodiumAI::model(RodiumAIModel::OpenAiGpt4o)
    ->temperature(0.5)
    ->topP(0.9)
    ->maxTokens(500)
    ->chat($messages);
```

## Streaming (SSE)

Follows [docs/api/streaming](https://www.rodiumai.io/docs/api/streaming): `data: …` lines, end with `data: [DONE]`.

```php
foreach (RodiumAI::model(RodiumAIModel::OpenAiGpt4o)->stream('Tell a short story.') as $delta) {
    echo $delta;
}
```

### Laravel — `StreamedResponse`

```php
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use RodiumAI\Facades\RodiumAI;

public function streamChat(Request $request): StreamedResponse
{
    return response()->stream(function () use ($request) {
        foreach (RodiumAI::stream($request->string('message')) as $delta) {
            echo 'data: ' . json_encode(['delta' => $delta]) . "\n\n";
            ob_flush();
            flush();
        }
        echo "data: [DONE]\n\n";
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'X-Accel-Buffering' => 'no',
    ]);
}
```

## Listing models

`GET /v1/models` — RODI pricing and metadata:

```php
$models = RodiumAI::models();

foreach ($models->ids() as $id) {
    echo $id . PHP_EOL;
}

$anthropic = $models->byProvider(RodiumAIProvider::Anthropic);
```

## Error handling

See [docs/api/errors](https://www.rodiumai.io/docs/api/errors).

| HTTP | SDK exception | Suggested action |
|------|---------------|------------------|
| 401 | `UnauthorizedException` | Check `RODIUMAI_API_KEY` |
| 402 | `InsufficientCreditsException` | Top up RODI credits (dashboard) |
| 429 | `RateLimitException` | Exponential backoff, then retry |
| 422 | `ValidationException` | Fix `model` / `messages` |
| 500+ | `RodiumAIException` | Retry once, then contact support |

```php
use RodiumAI\Exceptions\InsufficientCreditsException;
use RodiumAI\Exceptions\RodiumAIException;
use RodiumAI\Facades\RodiumAI;

try {
    $response = RodiumAI::chat('Test');
} catch (InsufficientCreditsException $e) {
    logger()->warning('Insufficient RODI', ['body' => $e->responseBody()]);
} catch (RodiumAIException $e) {
    logger()->error('Rodium AI', ['code' => $e->getCode(), 'body' => $e->responseBody()]);
}
```

## SDK reference

| Method | Returns | Description |
|--------|---------|-------------|
| `chat($messages, $options = [])` | `ChatResponse` | Non-streaming completion |
| `stream($messages, $options = [])` | `Generator<string>` | SSE text deltas |
| `models()` | `ModelCollection` | Catalogue + pricing |
| `model($id)` | `static` | Fluent: model |
| `temperature($f)` | `static` | Fluent: 0–2 |
| `topP($f)` | `static` | Fluent: 0–1 |
| `maxTokens($n)` | `static` | Fluent: token limit |
| `systemPrompt($s)` | `static` | Fluent: system message |

DTOs: `ChatResponse`, `ChatMessage`, `ModelCollection`.

## Versions

| Version | Notes |
|---------|--------|
| **v0.1.1** | Laravel 12 support (`illuminate/support` ^12) |
| **v0.1.0** | Initial release: chat, stream, models, enums, Facade |

Full history: [CHANGELOG.md](CHANGELOG.md).

## Testing & development

```bash
composer install
composer test                 # PHPUnit (mocked HTTP)
export RODIUMAI_API_KEY="…"
php bin/smoke-test.php        # Live API walkthrough in the terminal
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) and [docs/architecture.md](docs/architecture.md).

Maintainers: [docs/PUBLISHING.md](docs/PUBLISHING.md) (tags, Packagist).

## License

MIT — see [LICENSE](LICENSE).
