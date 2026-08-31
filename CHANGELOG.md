# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.0] - 2026-08-31

### Added

- Full gateway alignment: `modelInfo()`, `codingModels()`, `embeddings()`, `images()`, `videos()`, `transcribe()`, `speech()`, `messages()`, `wallet()`, `pricing()`
- `HttpTransport` internal HTTP layer (JSON, multipart, binary, streaming)
- `ChatResponse::costRodi()`, `ChatResponse::routing()` for RodiumAI extensions
- `ModelInfo` accessors: `provider()`, `displayName()`, `pricing()`, `status()`, `supportsTools()`, `supportsVision()`
- Exceptions: `ForbiddenException` (403), `NotFoundException` (404)
- `RodiumAIException::errorCode()`, `errorType()`; `RateLimitException::retryAfter()`
- Anthropic-shaped error envelope parsing for `/v1/messages`
- Chat payload passthrough for `tools`, `response_format`, `session_id`, and other upstream fields
- DTOs: `EmbeddingResponse`, `ImageResponse`, `VideoResponse`, `TranscriptionResponse`, `MessageResponse`, `WalletResponse`, `PricingCollection`

### Changed

- **Breaking:** Restored configurable `base_url` via `RODIUMAI_BASE_URL` (default `https://api.rodiumai.io/v1`)
- **Breaking:** `ModelInfo::contextWindow` now reads `rodiumai_capabilities.context_window` (gateway shape)
- Removed static enums `RodiumAIModel`, `RodiumAIProvider`, `RodiumAIModality` — use `models()` instead
- `model()` accepts string IDs only (from live API catalogue)
- Added i18n exception hints for 403/404 (`en`, `fr`, `es`)

### Fixed

- Laravel 10–13 compatibility via wide `illuminate/support` version range

## [0.1.1] - 2026-06-02

### Added

- Support Laravel 12 (`illuminate/support` ^12.0)

## [0.1.0] - 2026-06-01

### Added

- Initial release: chat, stream, models, Facade, typed exceptions
