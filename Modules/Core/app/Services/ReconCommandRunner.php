<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Modules\Core\Contracts\ReconCommandRunnerInterface;
use Modules\Core\DTOs\ReconCommandResult;

class ReconCommandRunner implements ReconCommandRunnerInterface
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('recon.tools_url', 'http://recon-tools:8080'), '/');
    }

    /**
     * @param  array<int, string>  $command
     */
    public function run(array $command, ?string $workingDirectory = null): ReconCommandResult
    {
        $tool = basename($command[0]);
        $args = array_slice($command, 1);
        $scanId = $this->extractScanId($workingDirectory, $command);

        Http::timeout(30)->post("{$this->baseUrl}/run", [
            'tool' => $tool,
            'args' => $args,
            'scan_id' => $scanId,
        ]);

        $statusData = $this->waitForCompletion($tool, $scanId);

        $response = Http::timeout(30)->get("{$this->baseUrl}/output/{$scanId}/{$tool}");
        $data = $response->json() ?? [];

        $exitCode = (int) ($statusData['exit_code'] ?? 1);
        $toolStatus = (string) ($statusData['status'] ?? 'unknown');
        $hasOutput = ($data['ready'] ?? false) === true;
        $successful = $hasOutput && $toolStatus === 'completed' && $exitCode === 0;

        return new ReconCommandResult(
            successful: $successful,
            output: $hasOutput ? implode("\n", $data['results'] ?? []) : '',
            errorOutput: $successful ? '' : ($data['error'] ?? $statusData['stderr'] ?? $statusData['error'] ?? 'Tool failed or timed out'),
            exitCode: $successful ? 0 : ($exitCode ?: 1),
        );
    }

    public static function scanOutputDirectory(int $scanId): string
    {
        $path = rtrim((string) config('recon.output_path'), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR.'scan-'.$scanId;

        File::ensureDirectoryExists($path);

        return $path;
    }

    /**
     * @return array<string, mixed>
     */
    private function waitForCompletion(string $tool, string $scanId): array
    {
        $maxWait = (int) config('recon.command_timeout', 7200);
        $pollInterval = max(1, (int) config('recon.poll_interval', 2));
        $waited = 0;

        while ($waited < $maxWait) {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/status/{$scanId}/{$tool}");
                $statusData = $response->json() ?? [];
                $status = $statusData['status'] ?? null;

                if (in_array($status, ['completed', 'failed', 'timeout'], true)) {
                    return $statusData;
                }
            } catch (\Exception) {
            }

            sleep($pollInterval);
            $waited += $pollInterval;
        }

        return ['status' => 'timeout'];
    }

    /**
     * @param  array<int, string>  $command
     */
    private function extractScanId(?string $workingDirectory, array $command): string
    {
        if ($workingDirectory !== null && preg_match('/scan-(\d+)/', $workingDirectory, $matches)) {
            return $matches[1];
        }

        foreach ($command as $argument) {
            if (preg_match('/scan-(\d+)/', (string) $argument, $matches)) {
                return $matches[1];
            }
        }

        throw new \RuntimeException('Unable to determine scan id from recon command.');
    }
}
