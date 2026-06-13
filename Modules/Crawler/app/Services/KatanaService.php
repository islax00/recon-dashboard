<?php

namespace Modules\Crawler\Services;

use Modules\Core\Contracts\ReconCommandRunnerInterface;
use Modules\Core\Contracts\ReconToolInterface;
use Modules\Core\DTOs\ReconResultDto;
use Modules\Core\DTOs\ScanDto;
use Modules\Core\Services\ReconCommandRunner;
use Modules\Crawler\Models\Endpoint;
use Modules\Crawler\Parsers\KatanaParser;
use Modules\Subdomain\Models\Subdomain;

class KatanaService implements ReconToolInterface
{
    public function __construct(
        private ReconCommandRunnerInterface $runner,
        private KatanaParser $parser,
    ) {}

    public function name(): string
    {
        return 'katana';
    }

    public function run(ScanDto $scan): ReconResultDto
    {
        $outputDirectory = ReconCommandRunner::scanOutputDirectory($scan->id);
        $outputFile = $outputDirectory.DIRECTORY_SEPARATOR.'endpoints.txt';
        $target = 'https://'.$scan->domain;

        $result = $this->runner->run([
            (string) config('recon.tools.katana', 'katana'),
            '-u', $target,
            '-silent',
            '-o', $outputFile,
        ]);

        $output = is_file($outputFile) ? (string) file_get_contents($outputFile) : $result->output;
        $items = $this->parser->parse($output);

        if ($items === [] && ! $result->successful) {
            $items = [['url' => $target, 'method' => 'GET']];
        }

        $subdomain = Subdomain::query()
            ->where('scan_id', $scan->id)
            ->where('hostname', $scan->domain)
            ->first();

        $stored = [];

        foreach ($items as $item) {
            $endpoint = Endpoint::query()->updateOrCreate(
                [
                    'scan_id' => $scan->id,
                    'url' => $item['url'],
                    'method' => $item['method'],
                ],
                [
                    'subdomain_id' => $subdomain?->id,
                    'status_code' => null,
                    'content_type' => str_ends_with(strtolower($item['url']), '.js') ? 'application/javascript' : null,
                ],
            );

            $stored[] = array_merge($item, ['endpoint_id' => $endpoint->id]);
        }

        return new ReconResultDto(
            scanId: $scan->id,
            tool: $this->name(),
            success: true,
            items: $stored,
            metadata: ['count' => count($stored)],
        );
    }
}
