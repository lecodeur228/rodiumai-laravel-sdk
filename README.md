# rodiumai/laravel-sdk

SDK PHP / Laravel officiel pour l’API [Rodium AI](https://www.rodiumai.io) — accès unifié aux modèles IA (OpenAI, Anthropic, Google, DeepSeek, MiniMax…) avec facturation en crédits **RODI** et recharge **Mobile Money**.

> API REST **compatible OpenAI** : mêmes endpoints et payloads que [rodiumai.io/docs](https://www.rodiumai.io/docs).

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rodiumai/laravel-sdk.svg)](https://packagist.org/packages/rodiumai/laravel-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/rodiumai/laravel-sdk.svg)](https://packagist.org/packages/rodiumai/laravel-sdk)
[![PHP Version](https://img.shields.io/packagist/php-v/rodiumai/laravel-sdk.svg)](https://packagist.org/packages/rodiumai/laravel-sdk)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Tests](https://github.com/lecodeur228/rodiumai-laravel-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/lecodeur228/rodiumai-laravel-sdk/actions)

## Liens

| Ressource | URL |
|-----------|-----|
| **Packagist** (installation Composer) | [packagist.org/packages/rodiumai/laravel-sdk](https://packagist.org/packages/rodiumai/laravel-sdk) |
| **Code source** | [github.com/lecodeur228/rodiumai-laravel-sdk](https://github.com/lecodeur228/rodiumai-laravel-sdk) |
| **Documentation API** | [rodiumai.io/docs](https://www.rodiumai.io/docs) |
| **Dashboard & clés API** | [rodiumai.io/dashboard](https://www.rodiumai.io/dashboard) |
| **Catalogue modèles** | [rodiumai.io/models](https://www.rodiumai.io/models) |

## Table des matières

- [Documentation officielle](#documentation-officielle)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Démarrage rapide](#démarrage-rapide)
- [Modèles typés (enum)](#modèles-typés-enum)
- [Paramètres chat](#paramètres-chat)
- [Streaming (SSE)](#streaming-sse)
- [Liste des modèles](#liste-des-modèles)
- [Gestion des erreurs](#gestion-des-erreurs)
- [Référence SDK](#référence-sdk)
- [Tests & développement](#tests--développement)
- [Contribuer](#contribuer)
- [Licence](#licence)

## Documentation officielle

| Sujet | Lien Rodium AI |
|--------|----------------|
| Quickstart | [rodiumai.io/docs](https://www.rodiumai.io/docs) |
| Vue d’ensemble API | [docs/api/overview](https://www.rodiumai.io/docs/api/overview) |
| Chat completions | [docs/api/chat-completions](https://www.rodiumai.io/docs/api/chat-completions) |
| Streaming SSE | [docs/api/streaming](https://www.rodiumai.io/docs/api/streaming) |
| Modèles | [docs/api/models](https://www.rodiumai.io/docs/api/models) · [Catalogue](https://www.rodiumai.io/models) |
| Erreurs HTTP | [docs/api/errors](https://www.rodiumai.io/docs/api/errors) |

Alignement détaillé SDK ↔ API : [docs/api-alignment.md](docs/api-alignment.md).

## Prérequis

- PHP **8.1+** avec extension `json`
- Laravel **10**, **11** ou **12** (optionnel — le client fonctionne en PHP pur)
- Laravel **12** : PHP **8.2+** → utilisez **`^0.1.1`** minimum
- Compte Rodium AI + clé API : [dashboard](https://www.rodiumai.io/dashboard)

## Installation

Le package est publié sur **Packagist** — aucune configuration `repositories` n’est nécessaire.

### Laravel 10 / 11

```bash
composer require rodiumai/laravel-sdk
```

### Laravel 12 (recommandé)

```bash
composer require rodiumai/laravel-sdk:^0.1.1
```

### Après `composer require`

1. Publier la config (optionnel mais recommandé) :

```bash
php artisan vendor:publish --tag=rodiumai-config
```

2. Ajouter ta clé API dans `.env` (voir [Configuration](#configuration)).

3. Le `ServiceProvider` et la Facade `RodiumAI` sont **auto-découverts** — rien à déclarer dans `bootstrap/providers.php`.

### PHP sans Laravel

```bash
composer require rodiumai/laravel-sdk
```

Puis instancier `RodiumAI\RodiumAIClient` directement (voir [Démarrage rapide](#démarrage-rapide)).

### Installation depuis GitHub (secours)

Uniquement si Packagist est indisponible :

```bash
composer config repositories.rodiumai-laravel-sdk vcs https://github.com/lecodeur228/rodiumai-laravel-sdk
composer require rodiumai/laravel-sdk:^0.1.1
```

## Configuration

Fichier `.env` :

```env
RODIUMAI_API_KEY=rd_sk_votre_cle_secrete
RODIUMAI_BASE_URL=https://api.rodiumai.io/v1
RODIUMAI_DEFAULT_MODEL=openai/gpt-4o
RODIUMAI_TIMEOUT=30
```

Ne jamais committer `.env` ni une clé API dans le dépôt.

## Démarrage rapide

### Laravel (Facade)

```php
use RodiumAI\Enums\RodiumAIModel;
use RodiumAI\Facades\RodiumAI;

$response = RodiumAI::model(RodiumAIModel::OpenAiGpt4o)
    ->temperature(0.7)
    ->maxTokens(300)
    ->chat('Explique Rodium AI en deux phrases.');

echo $response->content;
echo $response->totalTokens(); // tokens facturés
```

### PHP pur

```php
use RodiumAI\Enums\RodiumAIModel;
use RodiumAI\RodiumAIClient;

$client = new RodiumAIClient(
    apiKey: getenv('RODIUMAI_API_KEY'),
);

$response = $client
    ->model(RodiumAIModel::AnthropicClaudeSonnet46)
    ->chat('Bonjour !');

echo $response->content;
```

Équivalent [quickstart cURL / OpenAI SDK](https://www.rodiumai.io/docs) : `POST https://api.rodiumai.io/v1/chat/completions` avec `Authorization: Bearer {RODIUMAI_API_KEY}`.

## Modèles typés (enum)

Les **45+** modèles du catalogue sont exposés via `RodiumAIModel` — autocomplétion IDE et validation à l’exécution :

```php
use RodiumAI\Enums\RodiumAIModality;
use RodiumAI\Enums\RodiumAIModel;
use RodiumAI\Enums\RodiumAIProvider;

RodiumAI::model(RodiumAIModel::OpenAiGpt4o)->chat('…');

// Filtres
RodiumAIModel::forProvider(RodiumAIProvider::Google);
RodiumAIModel::forModality(RodiumAIModality::Text);

// Chat texte uniquement
RodiumAIModel::OpenAiGpt4o->supportsChatCompletion(); // true
```

Quand Rodium AI ajoute des modèles :

```bash
RODIUMAI_API_KEY="…" php bin/generate-model-enum.php
```

## Paramètres chat

Alignés sur [chat-completions](https://www.rodiumai.io/docs/api/chat-completions) :

| Paramètre API | SDK |
|---------------|-----|
| `model` | `->model()` / `RodiumAIModel` / `$options['model']` |
| `messages` | Tableau `{role, content}` ou `string` (→ message `user`) |
| `max_tokens` | `->maxTokens()` / `$options['max_tokens']` |
| `temperature` (0–2) | `->temperature()` / `$options['temperature']` |
| `top_p` (0–1) | `->topP()` / `$options['top_p']` |
| `stop` | `$options['stop']` |
| `stream` | Automatique avec `->stream()` |

```php
use RodiumAI\Data\ChatMessage;

$messages = [
    ChatMessage::system('Tu es un assistant Laravel.'),
    ChatMessage::user('C\'est quoi un Service Provider ?'),
];

$response = RodiumAI::model(RodiumAIModel::OpenAiGpt4o)
    ->temperature(0.5)
    ->topP(0.9)
    ->maxTokens(500)
    ->chat($messages);
```

## Streaming (SSE)

Conforme à [docs/api/streaming](https://www.rodiumai.io/docs/api/streaming) : lignes `data: …`, fin sur `data: [DONE]`.

```php
foreach (RodiumAI::model(RodiumAIModel::OpenAiGpt4o)->stream('Raconte une histoire courte.') as $delta) {
    echo $delta;
}
```

### Laravel — réponse `StreamedResponse`

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

## Liste des modèles

`GET /v1/models` — tarifs RODI et métadonnées :

```php
$models = RodiumAI::models();

foreach ($models->ids() as $id) {
    echo $id . PHP_EOL;
}

$anthropic = $models->byProvider(RodiumAIProvider::Anthropic);
```

## Gestion des erreurs

Voir [docs/api/errors](https://www.rodiumai.io/docs/api/errors).

| HTTP | Exception SDK | Action suggérée |
|------|---------------|-----------------|
| 401 | `UnauthorizedException` | Vérifier `RODIUMAI_API_KEY` |
| 402 | `InsufficientCreditsException` | Recharger des RODI (dashboard) |
| 429 | `RateLimitException` | Backoff exponentiel puis retry |
| 422 | `ValidationException` | Corriger `model` / `messages` |
| 500+ | `RodiumAIException` | Retry une fois, puis support |

```php
use RodiumAI\Exceptions\InsufficientCreditsException;
use RodiumAI\Exceptions\RodiumAIException;
use RodiumAI\Facades\RodiumAI;

try {
    $response = RodiumAI::chat('Test');
} catch (InsufficientCreditsException $e) {
    logger()->warning('RODI insuffisant', ['body' => $e->responseBody()]);
} catch (RodiumAIException $e) {
    logger()->error('Rodium AI', ['code' => $e->getCode(), 'body' => $e->responseBody()]);
}
```

## Référence SDK

| Méthode | Retour | Description |
|---------|--------|-------------|
| `chat($messages, $options = [])` | `ChatResponse` | Completion non-streaming |
| `stream($messages, $options = [])` | `Generator<string>` | Deltas texte SSE |
| `models()` | `ModelCollection` | Catalogue + pricing |
| `model($id)` | `static` | Fluent : modèle |
| `temperature($f)` | `static` | Fluent : 0–2 |
| `topP($f)` | `static` | Fluent : 0–1 |
| `maxTokens($n)` | `static` | Fluent : limite tokens |
| `systemPrompt($s)` | `static` | Fluent : message système |

DTOs : `ChatResponse`, `ChatMessage`, `ModelCollection`.

## Versions

| Version | Notes |
|---------|--------|
| **v0.1.1** | Support Laravel 12 (`illuminate/support` ^12) |
| **v0.1.0** | Première release : chat, stream, models, enums, Facade |

Historique : [CHANGELOG.md](CHANGELOG.md).

## Tests & développement

```bash
composer install
composer test                 # PHPUnit (HTTP mocké)
export RODIUMAI_API_KEY="…"
php bin/smoke-test.php        # Parcours live dans la console
```

## Contribuer

Voir [CONTRIBUTING.md](CONTRIBUTING.md) et [docs/architecture.md](docs/architecture.md).

Mainteneurs : guide [docs/PUBLISHING.md](docs/PUBLISHING.md) (tags, Packagist).

## Licence

MIT — voir [LICENSE](LICENSE).
