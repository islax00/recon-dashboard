<?php

namespace Modules\Network\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Concerns\InteractsWithScanPipeline;
use Modules\Network\Services\DnsxService;
use Modules\Reconnaissance\Models\Scan;

class RunDnsxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, InteractsWithScanPipeline, Queueable, SerializesModels;

    public function __construct(public Scan $scan) {}

    public function handle(DnsxService $service): void
    {
        $result = $service->run($this->scan->toDto());

        $this->logScan(
            $this->scan,
            $service->name(),
            sprintf('Resolved %d IP addresses', count($result->items)),
        );
    }
}
