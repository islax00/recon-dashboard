<?php

namespace Modules\Fingerprint\Services;

use Modules\Core\Contracts\ReconCommandRunnerInterface;
use Modules\Core\Contracts\ReconToolInterface;
use Modules\Core\DTOs\ReconResultDto;
use Modules\Core\DTOs\ScanDto;
use Modules\Core\Services\ReconCommandRunner;
use Modules\Fingerprint\Models\Technology;
use Modules\Fingerprint\Parsers\HttpxParser;
use Modules\Subdomain\Models\Subdomain;

class HttpxService implements ReconToolInterface
{
    public function __construct(
        private ReconCommandRunnerInterface $runner,
        private HttpxParser $parser,
    ) {}

    public function name(): string
    {
        return 'httpx';
    }

    public function run(ScanDto $scan): ReconResultDto
    {
        $outputDirectory = ReconCommandRunner::scanOutputDirectory($scan->id);
        $hostsFile = $outputDirectory.DIRECTORY_SEPARATOR.'hosts.txt';
        $outputFile = $outputDirectory.DIRECTORY_SEPARATOR.'httpx.json';

        $hostnames = Subdomain::query()
            ->where('scan_id', $scan->id)
            ->pluck('hostname')
            ->all();

        if ($hostnames === []) {
            $hostnames = [$scan->domain];
        }

        file_put_contents($hostsFile, implode(PHP_EOL, $hostnames));

        $result = $this->runner->run([
            (string) config('recon.tools.httpx', 'httpx'),
            '-l', $hostsFile,
            '-json',
            '-tech-detect',
            '-status-code',
            '-title',
            '-o', $outputFile,
        ]);

        $output = is_file($outputFile) ? (string) file_get_contents($outputFile) : $result->output;
        $parsed = $this->parser->parse($output);
        $items = [];

        foreach ($parsed as $row) {
            $hostname = parse_url((string) ($row['url'] ?? ''), PHP_URL_HOST) ?: ($row['input'] ?? $scan->domain);
            $subdomain = Subdomain::query()
                ->where('scan_id', $scan->id)
                ->where('hostname', $hostname)
                ->first();

            if ($subdomain !== null) {
                $subdomain->update([
                    'is_alive' => true,
                    'status_code' => $row['status_code'] ?? $row['status-code'] ?? null,
                    'title' => $row['title'] ?? null,
                    'ip_address' => $row['host'] ?? $row['a'][0] ?? $subdomain->ip_address,
                ]);
            }

            foreach ($row['tech'] ?? [] as $tech) {
                $technology = Technology::query()->updateOrCreate(
                    [
                        'scan_id' => $scan->id,
                        'subdomain_id' => $subdomain?->id,
                        'name' => is_array($tech) ? ($tech['name'] ?? 'unknown') : (string) $tech,
                    ],
                    [
                        'version' => is_array($tech) ? ($tech['version'] ?? null) : null,
                        'category' => is_array($tech) ? ($tech['category'] ?? 'unknown') : 'unknown',
                    ],
                );

                $items[] = [
                    'technology_id' => $technology->id,
                    'name' => $technology->name,
                    'hostname' => $hostname,
                ];
            }
        }

        if ($items === [] && ! $result->successful) {
            Subdomain::query()
                ->where('scan_id', $scan->id)
                ->update(['is_alive' => true]);
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
