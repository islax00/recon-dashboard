<?php

namespace Modules\Core\Concerns;

use Modules\Core\Enums\ScanStatus;
use Modules\Core\Events\ScanProgressUpdated;
use Modules\Reconnaissance\Models\Scan;
use Modules\Reconnaissance\Models\ScanLog;

trait InteractsWithScanPipeline
{
    protected function broadcastProgress(Scan $scan, string $stage, int $progress, ?string $message = null): void
    {
        ScanProgressUpdated::dispatch(
            scanId: $scan->id,
            userId: $scan->user_id,
            status: $scan->status instanceof ScanStatus ? $scan->status : ScanStatus::from($scan->status),
            stage: $stage,
            progress: $progress,
            message: $message,
        );
    }

    protected function logScan(Scan $scan, string $tool, string $message, string $level = 'info'): void
    {
        ScanLog::query()->create([
            'scan_id' => $scan->id,
            'tool' => $tool,
            'level' => $level,
            'message' => $message,
        ]);
    }
}
