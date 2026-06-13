<?php

namespace Modules\Reconnaissance\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Reconnaissance\Models\Scan;
use Modules\Reconnaissance\Services\PipelineService;

class RunScanPipelineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Scan $scan) {}

    public function handle(PipelineService $pipeline): void
    {
        $pipeline->run($this->scan);
    }
}
