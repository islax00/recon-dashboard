<?php

return [
    'tools_url'       => env('RECON_TOOLS_URL', 'http://recon-tools:8080'),
    'output_path'     => env('RECON_OUTPUT_PATH', storage_path('recon')),
    'command_timeout' => env('RECON_COMMAND_TIMEOUT', 300),
];