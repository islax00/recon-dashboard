<?php

namespace Modules\Core\Contracts;

use Modules\Core\DTOs\ReconCommandResult;

interface ReconCommandRunnerInterface
{
    /**
     * @param  array<int, string>  $command
     */
    public function run(array $command, ?string $workingDirectory = null): ReconCommandResult;
}
