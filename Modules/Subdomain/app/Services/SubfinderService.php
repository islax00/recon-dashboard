<?php

namespace Modules\Subdomain\Services;

use Modules\Core\Contracts\ReconCommandRunnerInterface;
use Modules\Core\Contracts\ReconToolInterface;
use Modules\Core\DTOs\ReconResultDto;
use Modules\Core\DTOs\ScanDto;
use Modules\Core\Services\ReconCommandRunner;
use Modules\Subdomain\Models\Subdomain;
use Modules\Subdomain\Parsers\SubfinderParser;

class SubfinderService implements ReconToolInterface
{
    public function __construct(
        private ReconCommandRunnerInterface $runner,
        private SubfinderParser $parser,
    ) {}

    public function name(): string
    {
        return 'subfinder';
    }

    public function run(ScanDto $scan): ReconResultDto
    {
        $outputDirectory = ReconCommandRunner::scanOutputDirectory($scan->id);
        $outputFile = $outputDirectory.DIRECTORY_SEPARATOR.'subdomains.txt';

        $result = $this->runner->run([
            (string) config('recon.tools.subfinder', 'subfinder'),
            '-d', $scan->domain,
            '-silent',
            '-o', $outputFile,
        ]);

        $output = is_file($outputFile) ? (string) file_get_contents($outputFile) : $result->output;
        $items = $this->parser->parse($output);

        if ($items === [] && ! $result->successful) {
            return new ReconResultDto(
                scanId: $scan->id,
                tool: $this->name(),
                success: false,
                error: $result->errorOutput ?: 'Subfinder failed',
            );
        }

        if ($items === []) {
            $items = [['hostname' => $scan->domain]];
        }

        foreach ($items as $item) {
            Subdomain::query()->updateOrCreate(
                ['scan_id' => $scan->id, 'hostname' => $item['hostname']],
                ['is_alive' => false],
            );
        }

        return new ReconResultDto(
            scanId: $scan->id,
            tool: $this->name(),
            success: true,
            items: $items,
            metadata: ['count' => count($items)],
        );
    }
}
