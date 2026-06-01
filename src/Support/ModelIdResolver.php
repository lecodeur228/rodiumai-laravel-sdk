<?php

namespace RodiumAI\Support;

use RodiumAI\Enums\RodiumAIModel;
use InvalidArgumentException;

/**
 * Resolves model identifiers for API requests (enum or validated string).
 */
final class ModelIdResolver
{
    public function resolve(string|RodiumAIModel $model): string
    {
        if ($model instanceof RodiumAIModel) {
            return $model->value;
        }

        if (RodiumAIModel::tryFrom($model) === null) {
            throw new InvalidArgumentException(
                "Unknown RodiumAI model \"{$model}\". "
                . 'Use RodiumAI\\Enums\\RodiumAIModel or run php bin/generate-model-enum.php after new models ship.'
            );
        }

        return $model;
    }
}
