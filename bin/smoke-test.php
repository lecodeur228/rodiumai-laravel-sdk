#!/usr/bin/env php
<?php

/**
 * Smoke test live RodiumAI API — affiche toutes les réponses dans la console.
 *
 * Usage:
 *   export RODIUMAI_API_KEY="rd_sk_..."
 *   php bin/smoke-test.php
 */

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

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
$baseUrl = rtrim(getenv('RODIUMAI_BASE_URL') ?: 'https://api.rodiumai.io/v1', '/');

if ($apiKey === '') {
    fwrite(STDERR, "Erreur: définis RODIUMAI_API_KEY dans l'environnement.\n");
    exit(1);
}

$locale = getenv('RODIUMAI_LOCALE') ?: null;
$model = getenv('RODIUMAI_DEFAULT_MODEL') ?: 'openai/gpt-4o';
$timeout = (int) (getenv('RODIUMAI_TIMEOUT') ?: 60);

echo PHP_EOL;
echo "  RodiumAI SDK v0.2 — smoke test live" . PHP_EOL;
echo "  API      : {$baseUrl}" . PHP_EOL;
echo "  Modèle   : {$model}" . PHP_EOL;

$client = new RodiumAIClient(
    apiKey: $apiKey,
    timeout: $timeout,
    defaultModel: $model,
    baseUrl: $baseUrl,
    locale: $locale,
);

$failed = false;
$collection = null;
$modelId = $model;

// ─── 1. Models ───────────────────────────────────────────────────────────────
section('1. GET /models — models()');

try {
    $collection = $client->models();
    ok($collection->count() . ' modèle(s)');
    echo '  Providers : ' . implode(', ', array_slice($collection->providerPrefixes(), 0, 5)) . PHP_EOL;
    $modelId = $collection->chatModels()[0]->id ?? $model;
} catch (Throwable $e) {
    $failed = true;
    fail(get_class($e) . ': ' . $e->getMessage());
}

// ─── 2. Coding models ────────────────────────────────────────────────────────
section('2. GET /models/coding — codingModels()');

try {
    $coding = $client->codingModels();
    ok($coding->count() . ' modèle(s) coding');
} catch (Throwable $e) {
    $failed = true;
    fail(get_class($e) . ': ' . $e->getMessage());
}

// ─── 3. Chat ─────────────────────────────────────────────────────────────────
section('3. POST /chat/completions — chat()');

try {
    $response = $client->model($modelId)->maxTokens(30)->chat('Reply with exactly: PONG');

    ok('ChatResponse reçue');
    echo "  content : {$response->content}" . PHP_EOL;
    echo "  tokens  : {$response->totalTokens()}" . PHP_EOL;
    if ($response->costRodi() !== null) {
        echo "  cost_rodi : {$response->costRodi()}" . PHP_EOL;
    }
} catch (Throwable $e) {
    $failed = true;
    fail(get_class($e) . ': ' . $e->getMessage());
}

// ─── 4. Stream ───────────────────────────────────────────────────────────────
section('4. POST /chat/completions — stream()');

try {
    echo "  Deltas : ";
    foreach ($client->model($modelId)->maxTokens(20)->stream('Say hi briefly.') as $delta) {
        echo $delta;
    }
    echo PHP_EOL;
    ok('Stream OK');
} catch (Throwable $e) {
    $failed = true;
    fail(get_class($e) . ': ' . $e->getMessage());
}

// ─── 5. Wallet ───────────────────────────────────────────────────────────────
section('5. GET /wallet — wallet()');

try {
    $wallet = $client->wallet();
    ok("balance_rodi = {$wallet->balanceRodi}");
} catch (Throwable $e) {
    $failed = true;
    fail(get_class($e) . ': ' . $e->getMessage());
}

// ─── 6. Pricing ──────────────────────────────────────────────────────────────
section('6. GET /pricing — pricing()');

try {
    $pricing = $client->pricing();
    ok($pricing->count() . ' entrée(s), currency=' . $pricing->currency());
} catch (Throwable $e) {
    $failed = true;
    fail(get_class($e) . ': ' . $e->getMessage());
}

section('Résumé');
if ($failed) {
    fail('Au moins un test a échoué.');
    exit(1);
}

ok('Tous les tests live sont passés.');
exit(0);
