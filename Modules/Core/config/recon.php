<?php

return [
    'execution' => env('RECON_EXECUTION', 'local'),
    'docker_service' => env('RECON_DOCKER_SERVICE', 'recon-tools'),
    'output_path' => env('RECON_OUTPUT_PATH', storage_path('recon')),
    'command_timeout' => (int) env('RECON_COMMAND_TIMEOUT', 300),
    'tools' => [
        'subfinder' => env('RECON_SUBFINDER_BIN', 'subfinder'),
        'httpx' => env('RECON_HTTPX_BIN', 'httpx'),
        'katana' => env('RECON_KATANA_BIN', 'katana'),
        'naabu' => env('RECON_NAABU_BIN', 'naabu'),
    ],
];
