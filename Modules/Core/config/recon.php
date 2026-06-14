<?php

return [
    'tools_url' => env('RECON_TOOLS_URL', 'http://recon-tools:8080'),
    'output_path' => env('RECON_OUTPUT_PATH', storage_path('recon')),
    'command_timeout' => env('RECON_COMMAND_TIMEOUT', 7200),
    'poll_interval' => env('RECON_POLL_INTERVAL', 2),
    'katana_max_targets' => env('RECON_KATANA_MAX_TARGETS', 25),
    'katana_depth' => env('RECON_KATANA_DEPTH', 2),
];
