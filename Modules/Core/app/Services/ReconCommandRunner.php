<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\File;
use Modules\Core\Contracts\ReconCommandRunnerInterface;
use Modules\Core\DTOs\ReconCommandResult;
use Symfony\Component\Process\Process;

class ReconCommandRunner implements ReconCommandRunnerInterface
{
    /**
     * @param  array<int, string>  $command
     */
    public function run(array $command, ?string $workingDirectory = null): ReconCommandResult
    {
        $process = Process::timeout((int) config('recon.command_timeout', 300))
            ->path($workingDirectory ?? base_path())
            ->run($this->wrapCommand($command));

        return new ReconCommandResult(
            successful: $process->successful(),
            output: $process->getOutput(),
            errorOutput: $process->getErrorOutput(),
            exitCode: $process->getExitCode() ?? 1,
        );
    }

    /**
     * @param  array<int, string>  $command
     * @return array<int, string>
     */
    protected function wrapCommand(array $command): array
    {
        if (config('recon.execution') !== 'docker') {
            return $command;
        }

        return array_merge(
            ['docker', 'compose', '-f', base_path('compose.yaml'), 'exec', '-T', (string) config('recon.docker_service', 'recon-tools')],
            $command,
        );
    }

    public static function scanOutputDirectory(int $scanId): string
    {
        $path = rtrim((string) config('recon.output_path'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'scan-'.$scanId;

        File::ensureDirectoryExists($path);

        return $path;
    }
}
