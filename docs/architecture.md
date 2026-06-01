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
        → Guzzle POST chat/completions
        → ChatResponse::fromArray()
```

On HTTP 4xx/5xx:

```
ClientException
    → ApiExceptionMapper::map()
    → UnauthorizedException | InsufficientCreditsException | …
```

## Fluent builder

Methods `model()`, `temperature()`, `topP()`, `maxTokens()`, `systemPrompt()` return **`clone $this`** so a singleton-bound client in Laravel is never mutated:

```php
RodiumAI::model(RodiumAIModel::OpenAiGpt4o)->chat('Hi'); // safe with Facade
```

## Streaming

```
RodiumAIClient::stream()
    → ChatPayloadBuilder (stream: true)
    → Guzzle POST with stream option
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
| `ModelIdResolver` | Validates / resolves model strings and enums |
| `ChatPayloadBuilder` | Builds request JSON |
| `ApiExceptionMapper` | HTTP errors → typed exceptions |
| `SseStreamReader` | Parses SSE lines |

Pass custom instances into `RodiumAIClient` constructor for advanced testing or middleware-style customization.
