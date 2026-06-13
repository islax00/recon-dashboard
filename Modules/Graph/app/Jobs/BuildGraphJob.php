<?php

namespace Modules\Graph\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Concerns\InteractsWithScanPipeline;
use Modules\Graph\Services\GraphBuilderService;
use Modules\Reconnaissance\Models\Scan;

class BuildGraphJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, InteractsWithScanPipeline, Queueable, SerializesModels;

    public function __construct(public Scan $scan) {}

    public function handle(GraphBuilderService $service): void
    {
        $graph = $service->build($this->scan);

        $this->logScan(
            $this->scan,
            'graph',
            sprintf('Built graph with %d nodes and %d edges', count($graph['nodes']), count($graph['edges'])),
        );
    }
}
