<?php

namespace Modules\Report\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Concerns\InteractsWithScanPipeline;
use Modules\Reconnaissance\Models\Scan;
use Modules\Report\Services\ReportAggregatorService;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, InteractsWithScanPipeline, Queueable, SerializesModels;

    public function __construct(public Scan $scan) {}

    public function handle(ReportAggregatorService $service): void
    {
        $report = $service->aggregate($this->scan);

        $this->logScan(
            $this->scan,
            'report',
            sprintf('Generated report with risk score %d', $report->risk_score),
        );
    }
}
