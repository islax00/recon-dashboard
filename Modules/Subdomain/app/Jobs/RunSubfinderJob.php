<?php

namespace Modules\Subdomain\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Concerns\InteractsWithScanPipeline;
use Modules\Reconnaissance\Models\Scan;
use Modules\Subdomain\Services\SubfinderService;

class RunSubfinderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, InteractsWithScanPipeline, Queueable, SerializesModels;

    public function __construct(public Scan $scan) {}

    public function handle(SubfinderService $service): void
    {
        $result = $service->run($this->scan->toDto());

        if (! $result->success) {
            $this->logScan($this->scan, $service->name(), $result->error ?? 'Subfinder failed', 'error');

            throw new \RuntimeException($result->error ?? 'Subfinder failed');
        }

        $this->logScan(
            $this->scan,
            $service->name(),
            sprintf('Discovered %d subdomains', count($result->items)),
        );
    }
}
