# Architecture

## Design goals

1. **Framework-agnostic core** — `RodiumAIClient` has no Laravel imports.
2. **Thin Laravel layer** — `ServiceProvider` + `Facade` only wire config and the container.
3. **Small internal units** — `Support/` classes are easy to test and replace.
4. **Official API parity** — behaviour matches [rodiumai.io/docs](https://www.rodiumai.io/docs).

## Request flow (chat)

```
Application
    → RodiumAIClient::chat()
        → ChatPayloadBuilder::build()
        → HttpTransport::requestJson(POST chat/completions)
        → ChatResponse::fromArray()
```

On HTTP 4xx/5xx:

```
ClientException
    → ApiExceptionMapper::map()
    → UnauthorizedException | InsufficientCreditsException | …
```

## HttpTransport

Central HTTP layer used by all endpoints:

| Method | Use case |
|--------|----------|
| `requestJson()` | JSON request/response (chat, embeddings, wallet, …) |
| `requestMultipart()` | Audio transcription file upload |
| `requestBinary()` | TTS audio bytes |
| `requestStream()` | SSE chat streaming |

Auth: `Authorization: Bearer {api_key}` on every request. `messages()` adds `x-api-key` and `anthropic-version` headers.

## Fluent builder

Methods `model()`, `temperature()`, `topP()`, `maxTokens()`, `systemPrompt()`, `language()` return **`clone $this`** so a singleton-bound client in Laravel is never mutated:

```php
RodiumAI::model('openai/gpt-4o')->chat('Hi'); // safe with Facade
```

## Streaming

```
RodiumAIClient::stream()
    → ChatPayloadBuilder (stream: true)
    → HttpTransport::requestStream()
    → SseStreamReader::readTextDeltas()
    → Generator<string>
```

## Laravel integration

```
.env → config/rodiumai.php
    → RodiumAIServiceProvider::register()
        → singleton RodiumAIClient
        → alias 'rodiumai'
    → Facade RodiumAI → app('rodiumai')
```

Package discovery is declared in `composer.json` → `extra.laravel`.

## Extension points

| Class | Role |
|-------|------|
| `HttpTransport` | HTTP I/O (JSON, multipart, binary, stream) |
| `ChatPayloadBuilder` | Builds chat request JSON + passthrough options |
| `ApiExceptionMapper` | HTTP errors → typed exceptions (OpenAI + Anthropic shapes) |
| `SseStreamReader` | Parses SSE lines |

Pass custom `HttpTransport` into `RodiumAIClient` constructor for advanced testing.

## Model catalogue

Models are **always fetched live** from `GET /v1/models`. `ModelInfo` parses gateway fields under `rodiumai_capabilities`, `rodiumai_pricing`, etc. — never hard-coded enums.
