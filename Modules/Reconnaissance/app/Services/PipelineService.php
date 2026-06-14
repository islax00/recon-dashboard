<?php

namespace Modules\Reconnaissance\Services;

use Modules\Core\Concerns\InteractsWithScanPipeline;
use Modules\Core\Enums\ScanStatus;
use Modules\Crawler\Jobs\RunKatanaJob;
use Modules\Fingerprint\Jobs\RunHttpxJob;
use Modules\Graph\Jobs\BuildGraphJob;
use Modules\JsAnalyzer\Jobs\AnalyzeJsFilesJob;
use Modules\Network\Jobs\RunDnsxJob;
use Modules\Reconnaissance\Jobs\RunScanPipelineJob;
use Modules\Reconnaissance\Models\Scan;
use Modules\Report\Jobs\GenerateReportJob;
use Modules\Subdomain\Jobs\RunSubfinderJob;
use Throwable;

class PipelineService
{
    use InteractsWithScanPipeline;

    /**
     * @var array<int, array{job: class-string, stage: string, progress: int}>
     */
    protected array $stages = [
        ['job' => RunSubfinderJob::class, 'stage' => 'subfinder', 'progress' => 15],
        // ['job' => RunDnsxJob::class, 'stage' => 'dnsx', 'progress' => 30],
        ['job' => RunKatanaJob::class, 'stage' => 'katana', 'progress' => 45],
        ['job' => AnalyzeJsFilesJob::class, 'stage' => 'js_analyzer', 'progress' => 60],
        ['job' => RunHttpxJob::class, 'stage' => 'httpx', 'progress' => 75],
        ['job' => GenerateReportJob::class, 'stage' => 'report', 'progress' => 90],
        ['job' => BuildGraphJob::class, 'stage' => 'graph', 'progress' => 100],
    ];

    public function dispatch(Scan $scan): void
    {
        RunScanPipelineJob::dispatch($scan);
    }

    public function run(Scan $scan): void
    {
        $scan->update([
            'status' => ScanStatus::Running,
            'started_at' => now(),
        ]);

        $this->broadcastProgress($scan->fresh(), 'pipeline', 0, 'Scan pipeline started');

        try {
            foreach ($this->stages as $stage) {
                dispatch_sync(new $stage['job']($scan));
                $this->broadcastProgress($scan->fresh(), $stage['stage'], $stage['progress']);
            }

            $scan->update([
                'status' => ScanStatus::Completed,
                'completed_at' => now(),
            ]);

            $this->broadcastProgress($scan->fresh(), 'completed', 100, 'Scan completed successfully');
        } catch (Throwable $exception) {
            $scan->update([
                'status' => ScanStatus::Failed,
                'completed_at' => now(),
            ]);

            $this->broadcastProgress($scan->fresh(), 'failed', 100, $exception->getMessage());
            $this->logScan($scan, 'pipeline', $exception->getMessage(), 'error');

            throw $exception;
        }
    }
}
