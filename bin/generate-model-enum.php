#!/usr/bin/env php
<?php

/**
 * Regénère src/Enums/RodiumAIModel.php depuis l'API RodiumAI.
 *
 *   RODIUMAI_API_KEY="rd_sk_..." php bin/generate-model-enum.php
 */

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use RodiumAI\RodiumAIClient;

$apiKey = getenv('RODIUMAI_API_KEY') ?: '';
if ($apiKey === '') {
    fwrite(STDERR, "RODIUMAI_API_KEY requis.\n");
    exit(1);
}

$client = new RodiumAIClient(apiKey: $apiKey);
$models = $client->models()->toArray();
usort($models, fn ($a, $b) => strcmp($a['id'], $b['id']));

function caseNameFromId(string $id): string
{
    [$provider, $name] = explode('/', $id, 2);
    $providerPart = match ($provider) {
        'openai' => 'OpenAi',
        'anthropic' => 'Anthropic',
        'google' => 'Google',
        'deepseek' => 'DeepSeek',
        'minimax' => 'MiniMax',
        default => str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $provider))),
    };

    $segments = preg_split('/[-_.]+/', $name) ?: [];
    $namePart = implode('', array_map(
        fn (string $s) => is_numeric($s) ? $s : ucfirst(strtolower($s)),
        $segments
    ));

    return $providerPart . $namePart;
}

function modalityFromModel(array $model): string
{
    $out = $model['rodiumai_capabilities']['output_modalities'][0] ?? 'text';

    return match ($out) {
        'image' => 'Image',
        'video' => 'Video',
        'audio' => 'Audio',
        'embedding' => 'Embedding',
        default => 'Text',
    };
}

$cases = [];
$modalityMap = [];

foreach ($models as $model) {
    $id = $model['id'];
    $case = caseNameFromId($id);
    $modality = modalityFromModel($model);

    if (isset($cases[$case])) {
        $case .= '_' . substr(md5($id), 0, 6);
    }

    $cases[$case] = $id;
    $modalityMap[$case] = $modality;
}

$caseLines = [];
foreach ($cases as $case => $value) {
    $caseLines[] = "    case {$case} = '{$value}';";
}

$matchArms = '';
foreach ($cases as $case => $value) {
    $mod = $modalityMap[$case];
    $matchArms .= "            self::{$case} => RodiumAIModality::{$mod},\n";
}

$generatedAt = gmdate('Y-m-d');
$caseBlock = implode("\n", $caseLines);

$content = <<<PHP
<?php

namespace RodiumAI\Enums;

/**
 * Modèles disponibles sur la plateforme RodiumAI (https://www.rodiumai.io/models).
 *
 * Généré automatiquement le {$generatedAt} via bin/generate-model-enum.php.
 * Régénérer lorsque de nouveaux modèles sont ajoutés à la plateforme.
 */
enum RodiumAIModel: string
{
{$caseBlock}

    public function provider(): RodiumAIProvider
    {
        return RodiumAIProvider::fromString(explode('/', \$this->value, 2)[0]);
    }

    public function modality(): RodiumAIModality
    {
        return match (\$this) {
{$matchArms}        };
    }

    /** Modèles utilisables avec {@see \\RodiumAI\\RodiumAIClient::chat()} / {@see stream()}. */
    public function supportsChatCompletion(): bool
    {
        return \$this->modality() === RodiumAIModality::Text;
    }

    public static function tryFromApiId(string \$id): ?self
    {
        return self::tryFrom(\$id);
    }

    /**
     * @return list<self>
     */
    public static function forProvider(RodiumAIProvider \$provider): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self \$m) => \$m->provider() === \$provider
        ));
    }

    /**
     * @return list<self>
     */
    public static function forModality(RodiumAIModality \$modality): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self \$m) => \$m->modality() === \$modality
        ));
    }
}

PHP;

$path = dirname(__DIR__) . '/src/Enums/RodiumAIModel.php';
file_put_contents($path, $content);
echo "Written {$path} (" . count($cases) . " models)\n";
