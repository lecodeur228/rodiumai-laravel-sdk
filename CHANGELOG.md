# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-06-01

### Added

- `RodiumAIClient` — chat completions, streaming SSE, list models
- Fluent builder: `model()`, `temperature()`, `topP()`, `maxTokens()`, `systemPrompt()`
- Laravel `ServiceProvider`, config publishable, `RodiumAI` Facade
- DTOs: `ChatResponse`, `ChatMessage`, `ModelCollection`
- Enums: `RodiumAIModel` (platform catalogue), `RodiumAIProvider`, `RodiumAIModality`
- Typed exceptions: 401, 402, 429, 422 + `RodiumAIException::responseBody()`
- Internal `Support/` classes (payload builder, SSE parser, exception mapper)
- PHPUnit tests (mocked HTTP) + GitHub Actions (PHP 8.1–8.3, Laravel 10–11)
- Scripts: `bin/smoke-test.php`, `bin/generate-model-enum.php`
- Documentation: README, CONTRIBUTING, api-alignment, architecture, publishing guide
