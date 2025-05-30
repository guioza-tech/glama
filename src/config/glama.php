<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default LLM Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default LLM provider that will be used.
    |
    */
    'default_provider' => env('GLAMA_DEFAULT_PROVIDER', 'deepseek'),

    /*
    |--------------------------------------------------------------------------
    | LLM Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the LLM providers for your application.
    |
    */
    'providers' => [
        'ollama' => [
            'model' => env('OLLAMA_DEFAULT_MODEL', 'llama3.2'),
            'host' => env('OLLAMA_HOST', 'http://localhost:11434'),
        ],
        'deepseek' =>[
            'host' => 'https://api.deepseek.com',
            'key'=> env('DEEPSEEK_KEY'),
            'model' => env('DEEPSEEK_MODEL', 'deepseek-reasoner')

        ]
    ],
];
