# Contributing to rodiumai/laravel-sdk

See the Dart SDK [CONTRIBUTING.md](../rodiumai/CONTRIBUTING.md) for shared design principles.

## Dynamic models only

Do **not** reintroduce static model/provider enums. The catalogue comes from `GET /v1/models`:

```php
$catalogue = $client->models();
$modelId = $catalogue->chatModels()[0]->id;
$client->model($modelId)->chat('…');
```

## Local setup

```bash
cd rodiumai-laravel-sdk
composer install
./vendor/bin/phpunit
RODIUMAI_API_KEY="…" php bin/smoke-test.php
```

## Pull request checklist

- [ ] `./vendor/bin/phpunit` passes
- [ ] README / CHANGELOG updated for breaking changes
- [ ] Aligned with [rodiumai.io/docs](https://www.rodiumai.io/docs)
