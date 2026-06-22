<?php

namespace RodiumAI\Support;

/**
 * Localized SDK messages and AI response language instructions.
 *
 * @see https://www.rodiumai.io/docs/api/errors
 */
final class RodiumAIMessages
{
    private const CATALOG = [
        'en' => [
            'unknown_error' => 'Unknown error',
            'unauthorized_hint' => 'Check RODIUMAI_API_KEY',
            'insufficient_credits_hint' => 'Top up RODI credits at rodiumai.io',
            'rate_limit_hint' => 'Implement exponential backoff',
            'validation_hint' => 'Check model and messages payload',
            'ai_response_instruction' => 'Always respond in English.',
        ],
        'fr' => [
            'unknown_error' => 'Erreur inconnue',
            'unauthorized_hint' => 'Vérifier RODIUMAI_API_KEY',
            'insufficient_credits_hint' => 'Recharger les crédits RODI sur rodiumai.io',
            'rate_limit_hint' => 'Implémenter un backoff exponentiel',
            'validation_hint' => 'Vérifier le modèle et les messages',
            'ai_response_instruction' => 'Réponds toujours en français.',
        ],
        'es' => [
            'unknown_error' => 'Error desconocido',
            'unauthorized_hint' => 'Verificar RODIUMAI_API_KEY',
            'insufficient_credits_hint' => 'Recargar créditos RODI en rodiumai.io',
            'rate_limit_hint' => 'Implementar backoff exponencial',
            'validation_hint' => 'Verificar el modelo y los mensajes',
            'ai_response_instruction' => 'Responde siempre en español.',
        ],
    ];

    public function __construct(
        public readonly string $unknownError,
        public readonly string $unauthorizedHint,
        public readonly string $insufficientCreditsHint,
        public readonly string $rateLimitHint,
        public readonly string $validationHint,
        public readonly string $aiResponseInstruction,
    ) {}

    public static function resolve(string $locale): self
    {
        $primary = strtolower(explode('-', str_replace('_', '-', $locale))[0]);
        $data = self::CATALOG[$primary] ?? self::CATALOG['en'];

        return new self(
            unknownError: $data['unknown_error'],
            unauthorizedHint: $data['unauthorized_hint'],
            insufficientCreditsHint: $data['insufficient_credits_hint'],
            rateLimitHint: $data['rate_limit_hint'],
            validationHint: $data['validation_hint'],
            aiResponseInstruction: $data['ai_response_instruction'],
        );
    }
}
