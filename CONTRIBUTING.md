# Contributing to rodiumai/laravel-sdk

## Dynamic models only

Do **not** reintroduce static model/provider enums. The catalogue comes from `GET /v1/models`:

```php
$catalogue = $client->models();
$modelId = $catalogue->chatModels()[0]->id;
$client->model($modelId)->chat('…');
```

## Test fixtures

PHPUnit fixtures must match the **real gateway JSON shape** (`rodiumai_capabilities`, not top-level `context_window`). See [`rodiumai_fastapi/app/api/v1/endpoints/models.py`](../../rodiumai_fastapi/app/api/v1/endpoints/models.py) and [rodiumai.io/docs](https://www.rodiumai.io/docs).

## README

Update [README.md](README.md) whenever you add or change a public SDK method.

## Local setup

```bash
cd rodiumai-laravel-sdk
composer install
./vendor/bin/phpunit
RODIUMAI_API_KEY="…" php bin/smoke-test.php
```

Local gateway:

```env
RODIUMAI_BASE_URL=http://localhost:8001/v1
```

## Pull request checklist

- [ ] `./vendor/bin/phpunit` passes
- [ ] README / CHANGELOG / api-alignment.md updated for API changes
- [ ] Aligned with [rodiumai.io/docs](https://www.rodiumai.io/docs)
