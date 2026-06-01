# Contributing to rodiumai/laravel-sdk

Thank you for helping improve the official PHP / Laravel SDK for [Rodium AI](https://www.rodiumai.io).

## Before you start

- Read the [official API docs](https://www.rodiumai.io/docs) — the SDK must stay aligned with them.
- Check [docs/api-alignment.md](docs/api-alignment.md) for how SDK methods map to REST endpoints.
- Open an issue for large changes before opening a PR.

## Development setup

```bash
cd rodiumai-laravel-sdk
composer install
cp .env.example .env   # add RODIUMAI_API_KEY for live smoke tests only
```

## Running tests

```bash
composer test          # PHPUnit (mocked HTTP — no API key required)
```

Optional live check (uses real credits):

```bash
export RODIUMAI_API_KEY="rd_sk_..."
php bin/smoke-test.php
```

## Project layout

```
src/
  RodiumAIClient.php       # Public entry point
  RodiumAIServiceProvider.php
  Facades/                 # Laravel facade
  Data/                    # Response DTOs
  Enums/                   # RodiumAIModel, provider, modality
  Exceptions/              # Typed HTTP errors
  Support/                 # Internal: payload builder, SSE parser, exception mapper
tests/
  Unit/
  Feature/
docs/                      # Contributor & API alignment docs
bin/
  smoke-test.php           # Manual live API walkthrough
  generate-model-enum.php  # Regenerate RodiumAIModel from GET /v1/models
```

Keep `Support/` classes small and focused. Public API changes belong on `RodiumAIClient` or DTOs.

## Updating the model enum

When Rodium AI ships new catalogue models:

```bash
RODIUMAI_API_KEY="..." php bin/generate-model-enum.php
composer test
```

Commit the regenerated `src/Enums/RodiumAIModel.php` with a note in `CHANGELOG.md`.

## Code style

- PHP 8.1+ features (readonly, enums, typed properties).
- PSR-4 autoloading, PSR-12 style.
- Prefer explicit types and docblocks on public methods.
- No real API keys in commits, tests, or documentation examples.

## Pull request checklist

- [ ] `composer test` passes.
- [ ] New behaviour has PHPUnit coverage (Guzzle mocks, no live HTTP in CI).
- [ ] README / `docs/` updated if behaviour or API surface changed.
- [ ] `CHANGELOG.md` updated under `[Unreleased]`.

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).
