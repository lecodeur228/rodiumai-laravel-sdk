<?php

/**
 * RodiumAI Laravel SDK configuration.
 *
 * @see https://www.rodiumai.io/docs
 */
return [

    /*
    |--------------------------------------------------------------------------
    | API key
    |--------------------------------------------------------------------------
    |
    | Secret key from https://www.rodiumai.io/dashboard (Bearer token).
    | Never commit this value — use RODIUMAI_API_KEY in .env only.
    |
    */
    'api_key' => env('RODIUMAI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | Official API root: https://api.rodiumai.io/v1
    |
    */
    'base_url' => env('RODIUMAI_BASE_URL', 'https://api.rodiumai.io/v1'),

    /*
    |--------------------------------------------------------------------------
    | Default model
    |--------------------------------------------------------------------------
    |
    | Provider-scoped id (e.g. openai/gpt-4o). Prefer RodiumAI\Enums\RodiumAIModel
    | in application code for IDE autocomplete and validation.
    |
    */
    'default_model' => env('RODIUMAI_DEFAULT_MODEL', 'openai/gpt-4o'),

    /*
    |--------------------------------------------------------------------------
    | HTTP timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('RODIUMAI_TIMEOUT', 30),

];
