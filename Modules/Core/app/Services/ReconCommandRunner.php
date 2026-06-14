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
        $tool   = $command[0];
        $args   = array_slice($command, 1);
        $scanId = $this->extractScanId($workingDirectory);

        Http::timeout(30)->post("{$this->baseUrl}/run", [
            'tool'    => $tool,
            'args'    => $args,
            'scan_id' => $scanId,
        ]);

        $this->waitForCompletion($tool, $scanId);

        $response = Http::timeout(30)->get("{$this->baseUrl}/output/{$scanId}/{$tool}");
        $data     = $response->json();

        $ready = $data['ready'] ?? false;

        return new ReconCommandResult(
            successful: $ready,
            output: $ready ? implode("\n", $data['results'] ?? []) : '',
            errorOutput: $ready ? '' : ($data['error'] ?? 'Tool failed or timed out'),
            exitCode: $ready ? 0 : 1,
        );
    }

    public static function scanOutputDirectory(int $scanId): string
    {
        $path = rtrim((string) config('recon.output_path'), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR.'scan-'.$scanId;

        File::ensureDirectoryExists($path);

        return $path;
    }

    private function waitForCompletion(string $tool, string $scanId): void
    {
        $maxWait = (int) config('recon.command_timeout', 7200);
        $waited  = 0;

        while ($waited < $maxWait) {
            sleep(15);
            $waited += 15;

            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/status/{$scanId}/{$tool}");
                $status   = $response->json('status');

                if (in_array($status, ['completed', 'failed', 'timeout'])) {
                    return;
                }
            } catch (\Exception) {
  
            }
        }
    }

    private function extractScanId(?string $workingDirectory): string
    {
        if ($workingDirectory && preg_match('/scan-(\d+)/', $workingDirectory, $matches)) {
            return $matches[1];
        }

        return (string) time();
    }
}