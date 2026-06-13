<?php

return [
    'provider' => env('INTELLIGENCE_PROVIDER', 'anthropic'),
    'model' => env('INTELLIGENCE_MODEL', 'claude-sonnet-4-20250514'),
    'api_key' => env('INTELLIGENCE_API_KEY'),
    'base_url' => env('INTELLIGENCE_BASE_URL', 'https://api.anthropic.com/v1/messages'),
];
