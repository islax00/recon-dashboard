<?php

namespace Modules\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Enums\ScanStatus;

class ScanProgressUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $scanId,
        public int $userId,
        public ScanStatus $status,
        public string $stage,
        public int $progress,
        public ?string $message = null,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('scans.'.$this->scanId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'scan.progress.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'scan_id' => $this->scanId,
            'status' => $this->status->value,
            'stage' => $this->stage,
            'progress' => $this->progress,
            'message' => $this->message,
        ];
    }
}
