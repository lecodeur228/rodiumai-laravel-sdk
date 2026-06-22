<?php

/**
 * RodiumAI Laravel SDK configuration.
 *
 * @see https://www.rodiumai.io/docs
 */
return [

    'api_key' => env('RODIUMAI_API_KEY'),

    'default_model' => env('RODIUMAI_DEFAULT_MODEL', 'openai/gpt-4o'),

    'timeout' => (int) env('RODIUMAI_TIMEOUT', 30),

    /*
    | Optional — localized SDK error hints (en, fr, es). Omit to use English.
    */
    'locale' => env('RODIUMAI_LOCALE'),

];
