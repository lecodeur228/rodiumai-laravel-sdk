<?php

namespace RodiumAI\Enums;

/**
 * Catégories de modèles (alignées sur https://www.rodiumai.io/models).
 */
enum RodiumAIModality: string
{
    case Text = 'text';
    case Image = 'image';
    case Video = 'video';
    case Audio = 'audio';
    case Embedding = 'embedding';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text',
            self::Image => 'Image',
            self::Video => 'Video',
            self::Audio => 'Audio',
            self::Embedding => 'Embedding',
        };
    }
}
