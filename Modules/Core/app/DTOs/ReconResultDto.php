<?php

namespace Modules\Core\DTOs;

readonly class ReconResultDto
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public int $scanId,
        public string $tool,
        public bool $success,
        public array $items = [],
        public array $metadata = [],
        public ?string $error = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            scanId: (int) $data['scan_id'],
            tool: (string) $data['tool'],
            success: (bool) ($data['success'] ?? true),
            items: $data['items'] ?? [],
            metadata: $data['metadata'] ?? [],
            error: $data['error'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'scan_id' => $this->scanId,
            'tool' => $this->tool,
            'success' => $this->success,
            'items' => $this->items,
            'metadata' => $this->metadata,
            'error' => $this->error,
        ];
    }
}
