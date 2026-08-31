# API alignment with Rodium AI

This package implements the [Rodium AI REST API](https://www.rodiumai.io/docs/api/overview). The API is **OpenAI-compatible**: same paths, JSON shapes, and Bearer authentication.

**Base URL:** configurable via `RODIUMAI_BASE_URL` (default `https://api.rodiumai.io/v1`).

## Endpoints implemented

| Official endpoint | SDK method | Notes |
|-------------------|------------|--------|
| `POST /v1/chat/completions` | `RodiumAIClient::chat()` | Non-streaming completion |
| `POST /v1/chat/completions` (`stream: true`) | `RodiumAIClient::stream()` | SSE text deltas |
| `GET /v1/models` | `RodiumAIClient::models()` | Catalogue + RODI metadata |
| `GET /v1/models/{id}` | `RodiumAIClient::modelInfo()` | Single model (public) |
| `GET /v1/models/coding` | `RodiumAIClient::codingModels()` | Coding-tagged subset (public) |
| `POST /v1/embeddings` | `RodiumAIClient::embeddings()` | Scope `embeddings:write` |
| `POST /v1/images/generations` | `RodiumAIClient::images()` | Text / image generation |
| `POST /v1/videos/generations` | `RodiumAIClient::videos()` | Long timeout recommended |
| `POST /v1/audio/transcriptions` | `RodiumAIClient::transcribe()` | Multipart upload |
| `POST /v1/audio/speech` | `RodiumAIClient::speech()` | Binary audio response |
| `POST /v1/messages` | `RodiumAIClient::messages()` | Anthropic Messages API |
| `GET /v1/wallet` | `RodiumAIClient::wallet()` | RODI balance extension |
| `GET /v1/pricing` | `RodiumAIClient::pricing()` | Per-model RODI pricing |

References:

- [Chat completions](https://www.rodiumai.io/docs/api/chat-completions)
- [Streaming (SSE)](https://www.rodiumai.io/docs/api/streaming)
- [Models](https://www.rodiumai.io/docs/api/models)
- [Embeddings](https://www.rodiumai.io/docs/api/embeddings)
- [Images](https://www.rodiumai.io/docs/api/images)
- [Videos](https://www.rodiumai.io/docs/api/videos)
- [Transcriptions](https://www.rodiumai.io/docs/api/transcriptions)
- [Speech](https://www.rodiumai.io/docs/api/speech)
- [Messages (Anthropic)](https://www.rodiumai.io/docs/api/messages)
- [Errors](https://www.rodiumai.io/docs/api/errors)

## Request parameters (`chat` / `stream`)

| API parameter | SDK support | How |
|---------------|-------------|-----|
| `model` | Yes | `->model()`, `$options['model']`, config `default_model`, or IDs from `models()` |
| `messages` | Yes | Array of `{role, content}` or shorthand string |
| `max_tokens` | Yes | `->maxTokens()` or `$options['max_tokens']` |
| `temperature` | Yes | `->temperature()` or `$options['temperature']` |
| `top_p` | Yes | `->topP()` or `$options['top_p']` |
| `stream` | Yes | Set automatically by `stream()` |
| `stop` | Yes | `$options['stop']` |
| `tools`, `tool_choice`, `response_format`, `session_id`, … | Yes | Passthrough via `$options` |

## Authentication

```
Authorization: Bearer {RODIUMAI_API_KEY}
```

For `POST /v1/messages` and audio endpoints, the gateway also accepts `x-api-key` — the SDK sends both Bearer and `x-api-key` for `messages()`.

Configure via `.env` → `config/rodiumai.php` → `RodiumAIClient` constructor.

## Response extensions (RodiumAI)

| Field | SDK accessor |
|-------|--------------|
| `usage.cost_rodi` | `ChatResponse::costRodi()` |
| `rodiumai_routing` | `ChatResponse::routing()` |
| `rodiumai_capabilities.*` | `ModelInfo` accessors |
| `rodiumai_pricing` | `ModelInfo::pricing()` |

## Error handling

| HTTP | Official name | SDK exception |
|------|---------------|---------------|
| 401 | Unauthorized | `UnauthorizedException` |
| 402 | Insufficient Credits | `InsufficientCreditsException` |
| 403 | Forbidden | `ForbiddenException` |
| 404 | Not Found | `NotFoundException` |
| 422 | Validation Error | `ValidationException` |
| 429 | Rate Limited | `RateLimitException` (`retryAfter()`) |
| Other | — | `RodiumAIException` |

Both OpenAI-shaped and Anthropic-shaped error envelopes are parsed. Use `$exception->errorCode()`, `errorType()`, and `responseBody()`.

## Not in scope (v0.2.x)

- `POST /v1/responses` (partial gateway support) — use REST directly or OpenAI SDK
- OpenAI PHP SDK proxy mode — users can set `base_url` on OpenAI's client per [quickstart](https://www.rodiumai.io/docs)

Pull requests that extend coverage should link to the relevant official doc page.
