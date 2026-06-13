<?php

namespace Modules\Core\DTOs;

use Carbon\CarbonInterface;
use Modules\Core\Enums\ScanStatus;

readonly class ScanDto
{
    /**
     * @param  array<string, mixed>|null  $options
     */
    public function __construct(
        public int $id,
        public int $userId,
        public string $domain,
        public ScanStatus $status,
        public ?array $options = null,
        public ?CarbonInterface $startedAt = null,
        public ?CarbonInterface $completedAt = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            userId: (int) $data['user_id'],
            domain: (string) $data['domain'],
            status: $data['status'] instanceof ScanStatus
                ? $data['status']
                : ScanStatus::from((string) $data['status']),
            options: $data['options'] ?? null,
            startedAt: isset($data['started_at']) ? $data['started_at'] : null,
            completedAt: isset($data['completed_at']) ? $data['completed_at'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'domain' => $this->domain,
            'status' => $this->status->value,
            'options' => $this->options,
            'started_at' => $this->startedAt?->toIso8601String(),
            'completed_at' => $this->completedAt?->toIso8601String(),
        ];
    }
}
