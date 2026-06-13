<?php

namespace Modules\Fingerprint\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Concerns\InteractsWithScanPipeline;
use Modules\Fingerprint\Services\HttpxService;
use Modules\Reconnaissance\Models\Scan;

class RunHttpxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, InteractsWithScanPipeline, Queueable, SerializesModels;

    public function __construct(public Scan $scan) {}

    public function handle(HttpxService $service): void
    {
        $result = $service->run($this->scan->toDto());

        $this->logScan(
            $this->scan,
            $service->name(),
            sprintf('Fingerprinted %d technologies', count($result->items)),
        );
    }
}
