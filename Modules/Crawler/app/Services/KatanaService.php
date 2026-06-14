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
        $targetsFile = $outputDirectory.DIRECTORY_SEPARATOR.'katana-targets.txt';
        $maxTargets = (int) config('recon.katana_max_targets', 25);

        $hostnames = Subdomain::query()
            ->where('scan_id', $scan->id)
            ->where('is_alive', true)
            ->orderBy('id')
            ->limit($maxTargets)
            ->pluck('hostname')
            ->all();

        if ($hostnames === []) {
            $hostnames = Subdomain::query()
                ->where('scan_id', $scan->id)
                ->orderBy('id')
                ->limit($maxTargets)
                ->pluck('hostname')
                ->all();
        }

        if ($hostnames === []) {
            $hostnames = [$scan->domain];
        }

        $targets = array_map(function (string $hostname): string {
            $normalized = preg_replace('#^https?://#', '', strtolower(trim($hostname))) ?? trim($hostname);

            return 'https://'.$normalized;
        }, $hostnames);

        file_put_contents($targetsFile, implode(PHP_EOL, array_unique($targets)));

        $result = $this->runner->run([
            (string) config('recon.tools.katana', 'katana'),
            '-list', $targetsFile,
            '-silent',
            '-jc',
            '-d', (string) config('recon.katana_depth', 2),
            '-o', $outputFile,
        ]);

        $output = is_file($outputFile) ? (string) file_get_contents($outputFile) : $result->output;
        $items = $this->parser->parse($output);

        if ($items === [] && ! $result->successful) {
            $items = [['url' => 'https://'.$scan->domain, 'method' => 'GET']];
        }

        $stored = [];

        foreach ($items as $item) {
            $hostname = parse_url($item['url'], PHP_URL_HOST) ?: $scan->domain;

            $subdomain = Subdomain::query()
                ->where('scan_id', $scan->id)
                ->where('hostname', $hostname)
                ->first();

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
            metadata: ['count' => count($stored), 'targets' => count($targets)],
        );
    }
}
