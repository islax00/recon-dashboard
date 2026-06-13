<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\File;
use Modules\Core\Contracts\ReconCommandRunnerInterface;
use Modules\Core\DTOs\ReconCommandResult;

class FakeReconCommandRunner implements ReconCommandRunnerInterface
{
    /**
     * @param  array<int, string>  $command
     */
    public function run(array $command, ?string $workingDirectory = null): ReconCommandResult
    {
        $binary = basename($command[0]);
        $outputFile = $this->outputFileArgument($command);
        $content = match ($binary) {
            'subfinder' => $this->subfinderOutput($command),
            'katana' => $this->katanaOutput($command),
            'httpx' => $this->httpxOutput($command),
            default => '',
        };

        if ($outputFile !== null) {
            File::ensureDirectoryExists(dirname($outputFile));
            file_put_contents($outputFile, $content);
        }

        return new ReconCommandResult(
            successful: true,
            output: $content,
            errorOutput: '',
            exitCode: 0,
        );
    }

    /**
     * @param  array<int, string>  $command
     */
    protected function outputFileArgument(array $command): ?string
    {
        $index = array_search('-o', $command, true);

        if ($index === false) {
            return null;
        }

        return $command[$index + 1] ?? null;
    }

    /**
     * @param  array<int, string>  $command
     */
    protected function subfinderOutput(array $command): string
    {
        $domain = $this->optionValue($command, '-d') ?? 'example.com';

        return implode(PHP_EOL, [
            $domain,
            'api.'.$domain,
        ]);
    }

    /**
     * @param  array<int, string>  $command
     */
    protected function katanaOutput(array $command): string
    {
        $target = $this->optionValue($command, '-u') ?? 'https://example.com';

        return implode(PHP_EOL, [
            $target,
            rtrim($target, '/').'/app.js',
        ]);
    }

    /**
     * @param  array<int, string>  $command
     */
    protected function httpxOutput(array $command): string
    {
        return collect([
            [
                'url' => 'https://example.com',
                'input' => 'example.com',
                'status_code' => 200,
                'title' => 'Example',
                'tech' => [
                    ['name' => 'Nginx', 'version' => '1.24', 'category' => 'server'],
                ],
            ],
            [
                'url' => 'https://api.example.com',
                'input' => 'api.example.com',
                'status_code' => 200,
                'title' => 'API',
                'tech' => [
                    ['name' => 'Laravel', 'version' => '13', 'category' => 'framework'],
                ],
            ],
        ])->map(fn (array $row) => json_encode($row))->implode(PHP_EOL);
    }

    /**
     * @param  array<int, string>  $command
     */
    protected function optionValue(array $command, string $option): ?string
    {
        $index = array_search($option, $command, true);

        if ($index === false) {
            return null;
        }

        return $command[$index + 1] ?? null;
    }
}
