# API alignment with Rodium AI

This package implements the [Rodium AI REST API](https://www.rodiumai.io/docs/api/overview). The API is **OpenAI-compatible**: same paths, JSON shapes, and Bearer authentication.

**Base URL:** `https://api.rodiumai.io/v1`

## Endpoints implemented

| Official endpoint | SDK method | Notes |
|-------------------|------------|--------|
| `POST /v1/chat/completions` | `RodiumAIClient::chat()` | Non-streaming completion |
| `POST /v1/chat/completions` (`stream: true`) | `RodiumAIClient::stream()` | SSE text deltas |
| `GET /v1/models` | `RodiumAIClient::models()` | Catalogue + RODI pricing metadata |

References:

- [Chat completions](https://www.rodiumai.io/docs/api/chat-completions)
- [Streaming (SSE)](https://www.rodiumai.io/docs/api/streaming)
- [List models](https://www.rodiumai.io/docs/api/models)
- [Errors](https://www.rodiumai.io/docs/api/errors)

## Request parameters (`chat` / `stream`)

| API parameter | SDK support | How |
|---------------|-------------|-----|
| `model` | Yes | `->model()`, `$options['model']`, config `default_model`, or `RodiumAIModel` enum |
| `messages` | Yes | Array of `{role, content}` or shorthand string (converted to user message) |
| `max_tokens` | Yes | `->maxTokens()` or `$options['max_tokens']` |
| `temperature` | Yes | `->temperature()` or `$options['temperature']` (0–2) |
| `top_p` | Yes | `->topP()` or `$options['top_p']` (0–1) |
| `stream` | Yes | Set automatically by `stream()` |
| `stop` | Yes | `$options['stop']` (string or array) |

Official docs: [chat-completions parameters](https://www.rodiumai.io/docs/api/chat-completions).

## Authentication

```
Authorization: Bearer {RODIUMAI_API_KEY}
```

Configure via `.env` → `config/rodiumai.php` → `RodiumAIClient` constructor.

## Streaming

Per [streaming docs](https://www.rodiumai.io/docs/api/streaming):

- Request body includes `"stream": true`.
- Response lines use `data: {json}` format.
- Stream ends at `data: [DONE]`.

The SDK yields only non-null `choices[0].delta.content` strings.

## Error handling

| HTTP | Official name | SDK exception |
|------|---------------|---------------|
| 401 | Unauthorized | `UnauthorizedException` |
| 402 | Insufficient Credits | `InsufficientCreditsException` |
| 429 | Rate Limited | `RateLimitException` |
| 422 | Validation Error | `ValidationException` |
| Other | — | `RodiumAIException` |

Use `$exception->responseBody()` for the decoded JSON error envelope when present.

Official reference: [API errors](https://www.rodiumai.io/docs/api/errors).

## Model IDs

Use provider-scoped slugs (`openai/gpt-4o`, `anthropic/claude-sonnet-4-6`, …) as documented on the [models catalogue](https://www.rodiumai.io/models).

The `RodiumAIModel` enum mirrors `GET /v1/models`. Regenerate with:

```bash
RODIUMAI_API_KEY="..." php bin/generate-model-enum.php
```

## Not in scope (v0.x)

- Image / video generation endpoints (use catalogue models via future SDK methods or REST directly).
- OpenAI PHP SDK proxy mode (users can still set `base_url` on OpenAI’s client per [quickstart](https://www.rodiumai.io/docs)).

Pull requests that extend coverage should link to the relevant official doc page.
