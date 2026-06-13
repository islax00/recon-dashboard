<?php

namespace Modules\Core\DTOs;

readonly class ReconCommandResult
{
    public function __construct(
        public bool $successful,
        public string $output,
        public string $errorOutput,
        public int $exitCode,
    ) {}
}
