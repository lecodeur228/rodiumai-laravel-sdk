#!/usr/bin/env php
<?php

/**
 * Smoke test live RodiumAI API — affiche toutes les réponses dans la console.
 *
 * Usage:
 *   export RODIUMAI_API_KEY="rd_sk_..."
 *   php bin/smoke-test.php
 *
 * Ou en une ligne:
 *   RODIUMAI_API_KEY="rd_sk_..." php bin/smoke-test.php
 */

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use RodiumAI\Enums\RodiumAIModel;
use RodiumAI\Enums\RodiumAIModality;
use RodiumAI\Enums\RodiumAIProvider;
use RodiumAI\RodiumAIClient;

function line(string $char = '─', int $width = 60): void
{
    echo str_repeat($char, $width) . PHP_EOL;
}

function section(string $title): void
{
    echo PHP_EOL;
    line('═');
    echo "  {$title}" . PHP_EOL;
    line('═');
}

function ok(string $message): void
{
    echo "  ✓ {$message}" . PHP_EOL;
}

function fail(string $message): void
{
    echo "  ✗ {$message}" . PHP_EOL;
}

function dumpJson(mixed $data): void
{
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
}

$apiKey = getenv('RODIUMAI_API_KEY') ?: '';

if ($apiKey === '') {
    fwrite(STDERR, "Erreur: définis RODIUMAI_API_KEY dans l'environnement.\n");
    fwrite(STDERR, "  export RODIUMAI_API_KEY=\"rd_sk_...\"\n");
    fwrite(STDERR, "  php bin/smoke-test.php\n");
    exit(1);
}

$baseUrl = getenv('RODIUMAI_BASE_URL') ?: 'https://api.rodiumai.io/v1';
$model = getenv('RODIUMAI_DEFAULT_MODEL') ?: 'openai/gpt-4o';
$timeout = (int) (getenv('RODIUMAI_TIMEOUT') ?: 60);

echo PHP_EOL;
echo "  RodiumAI SDK — smoke test live" . PHP_EOL;
echo "  Base URL : {$baseUrl}" . PHP_EOL;
echo "  Modèle   : {$model}" . PHP_EOL;
echo "  Clé API  : " . substr($apiKey, 0, 12) . '…' . substr($apiKey, -4) . PHP_EOL;

$client = new RodiumAIClient(
    apiKey: $apiKey,
    baseUrl: $baseUrl,
    timeout: $timeout,
    defaultModel: $model,
);

$failed = false;

// ─── 1. Models ───────────────────────────────────────────────────────────────
section('1. GET /models — models()');

try {
    $collection = $client->models();
    $all = $collection->toArray();
    $ids = $collection->ids();

    ok(count($ids) . ' modèle(s) disponible(s)');
    echo PHP_EOL . "  IDs (premiers 10) :" . PHP_EOL;
    foreach (array_slice($ids, 0, 10) as $id) {
        echo "    - {$id}" . PHP_EOL;
    }
    if (count($ids) > 10) {
        echo "    … et " . (count($ids) - 10) . ' autre(s)' . PHP_EOL;
    }

    echo PHP_EOL . "  Filtre byProvider('anthropic') :" . PHP_EOL;
    $anthropic = $collection->byProvider('anthropic');
    foreach (array_slice($anthropic->ids(), 0, 5) as $id) {
        echo "    - {$id}" . PHP_EOL;
    }

    echo PHP_EOL . "  Réponse brute (1er modèle) :" . PHP_EOL;
    if ($all !== []) {
        dumpJson($all[0]);
    }
} catch (Throwable $e) {
    $failed = true;
    fail(get_class($e) . ' [' . $e->getCode() . '] ' . $e->getMessage());
}

// ─── 2. Chat simple (string) ─────────────────────────────────────────────────
section('2. POST /chat/completions — chat(string)');

try {
    $response = $client->chat('Réponds exactement avec le mot: PONG (rien d\'autre).');

    ok('ChatResponse reçue');
    echo PHP_EOL . "  Propriétés DTO :" . PHP_EOL;
    echo "    id            : {$response->id}" . PHP_EOL;
    echo "    model         : {$response->model}" . PHP_EOL;
    echo "    content       : {$response->content}" . PHP_EOL;
    echo "    finishReason  : {$response->finishReason}" . PHP_EOL;
    echo "    totalTokens() : {$response->totalTokens()}" . PHP_EOL;

    echo PHP_EOL . "  usage :" . PHP_EOL;
    dumpJson($response->usage);

    echo PHP_EOL . "  raw (réponse API complète) :" . PHP_EOL;
    dumpJson($response->raw);
} catch (Throwable $e) {
    $failed = true;
    fail(get_class($e) . ' [' . $e->getCode() . '] ' . $e->getMessage());
}

// ─── 3. Enum RodiumAIModel ───────────────────────────────────────────────────
section('3. Enums — RodiumAIModel / Provider / Modality');

echo "  Enum cases : " . count(RodiumAIModel::cases()) . PHP_EOL;
echo "  Exemple    : " . RodiumAIModel::OpenAiGpt4o->name . " => " . RodiumAIModel::OpenAiGpt4o->value . PHP_EOL;
echo "  Provider   : " . RodiumAIModel::OpenAiGpt4o->provider()->label() . PHP_EOL;
echo "  Modality   : " . RodiumAIModel::OpenAiGpt4o->modality()->label() . PHP_EOL;
echo PHP_EOL . "  Modèles texte Anthropic (" . count(RodiumAIModel::forProvider(RodiumAIProvider::Anthropic)) . ") :" . PHP_EOL;
foreach (array_slice(RodiumAIModel::forProvider(RodiumAIProvider::Anthropic), 0, 3) as $m) {
    echo "    - {$m->name} ({$m->value})" . PHP_EOL;
}
echo PHP_EOL . "  Modèles image :" . PHP_EOL;
foreach (RodiumAIModel::forModality(RodiumAIModality::Image) as $m) {
    echo "    - {$m->value}" . PHP_EOL;
}

// ─── 4. Chat fluent builder ──────────────────────────────────────────────────
section('4. POST /chat/completions — fluent builder + enum');

try {
    $response = $client
        ->model(RodiumAIModel::OpenAiGpt4o)
        ->temperature(0.3)
        ->maxTokens(30)
        ->systemPrompt('Tu réponds toujours en français, en une seule phrase courte.')
        ->chat([
            ['role' => 'user', 'content' => 'Quelle est la capitale du Togo ?'],
        ]);

    ok('Fluent builder OK');
    echo PHP_EOL . "  content : {$response->content}" . PHP_EOL;
    echo "  model   : {$response->model}" . PHP_EOL;
    echo "  tokens  : {$response->totalTokens()}" . PHP_EOL;
} catch (Throwable $e) {
    $failed = true;
    fail(get_class($e) . ' [' . $e->getCode() . '] ' . $e->getMessage());
}

// ─── 5. Streaming ─────────────────────────────────────────────────────────────
section('5. POST /chat/completions — stream()');

try {
    echo "  Deltas en direct : ";
    $full = '';
    $chunkCount = 0;

    foreach ($client->model(RodiumAIModel::OpenAiGpt4o)->maxTokens(40)->stream('Dis «Bonjour RodiumAI» en une courte phrase.') as $delta) {
        echo $delta;
        $full .= $delta;
        $chunkCount++;
        flush();
    }

    echo PHP_EOL . PHP_EOL;
    ok("{$chunkCount} delta(s) reçu(s)");
    echo "  Texte complet : {$full}" . PHP_EOL;
} catch (Throwable $e) {
    $failed = true;
    fail(get_class($e) . ' [' . $e->getCode() . '] ' . $e->getMessage());
}

// ─── 6. ChatMessage DTO (optionnel) ──────────────────────────────────────────
section('6. ChatMessage DTO → toArray()');

use RodiumAI\Data\ChatMessage;

$messages = [
    ChatMessage::system('Tu es un assistant de test.'),
    ChatMessage::user('Ping'),
];
echo "  Messages construits :" . PHP_EOL;
dumpJson(array_map(fn (ChatMessage $m) => $m->toArray(), $messages));

// ─── Résumé ──────────────────────────────────────────────────────────────────
section('Résumé');

if ($failed) {
    fail('Au moins un test a échoué.');
    exit(1);
}

ok('Tous les tests live sont passés.');
echo PHP_EOL;
exit(0);
