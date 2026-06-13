<?php

use Carbon\Carbon;
use Modules\Core\DTOs\ReconResultDto;
use Modules\Core\DTOs\ScanDto;
use Modules\Core\Enums\NodeType;
use Modules\Core\Enums\ScanStatus;
use Modules\Core\Events\ScanProgressUpdated;

test('scan status enum matches database values', function () {
    expect(ScanStatus::cases())->toHaveCount(4)
        ->and(ScanStatus::Pending->value)->toBe('pending')
        ->and(ScanStatus::Running->value)->toBe('running')
        ->and(ScanStatus::Completed->value)->toBe('completed')
        ->and(ScanStatus::Failed->value)->toBe('failed');
});

test('scan status identifies terminal states', function () {
    expect(ScanStatus::Pending->isTerminal())->toBeFalse()
        ->and(ScanStatus::Running->isTerminal())->toBeFalse()
        ->and(ScanStatus::Completed->isTerminal())->toBeTrue()
        ->and(ScanStatus::Failed->isTerminal())->toBeTrue();
});

test('node type enum matches graph schema', function () {
    expect(NodeType::Domain->value)->toBe('domain')
        ->and(NodeType::Subdomain->value)->toBe('subdomain')
        ->and(NodeType::Ip->value)->toBe('ip')
        ->and(NodeType::Endpoint->value)->toBe('endpoint')
        ->and(NodeType::JsFile->value)->toBe('js_file')
        ->and(NodeType::Technology->value)->toBe('technology');
});

test('scan dto round trips through array representation', function () {
    $startedAt = Carbon::parse('2026-06-13 10:00:00');

    $dto = ScanDto::fromArray([
        'id' => 1,
        'user_id' => 5,
        'domain' => 'example.com',
        'status' => ScanStatus::Running,
        'options' => ['depth' => 2],
        'started_at' => $startedAt,
        'completed_at' => null,
    ]);

    expect($dto->id)->toBe(1)
        ->and($dto->userId)->toBe(5)
        ->and($dto->domain)->toBe('example.com')
        ->and($dto->status)->toBe(ScanStatus::Running)
        ->and($dto->options)->toBe(['depth' => 2])
        ->and($dto->toArray())->toMatchArray([
            'id' => 1,
            'user_id' => 5,
            'domain' => 'example.com',
            'status' => 'running',
            'options' => ['depth' => 2],
            'completed_at' => null,
        ]);
});

test('recon result dto round trips through array representation', function () {
    $dto = ReconResultDto::fromArray([
        'scan_id' => 3,
        'tool' => 'subfinder',
        'success' => true,
        'items' => [
            ['hostname' => 'api.example.com'],
        ],
        'metadata' => ['count' => 1],
    ]);

    expect($dto->scanId)->toBe(3)
        ->and($dto->tool)->toBe('subfinder')
        ->and($dto->success)->toBeTrue()
        ->and($dto->items)->toHaveCount(1)
        ->and($dto->toArray())->toMatchArray([
            'scan_id' => 3,
            'tool' => 'subfinder',
            'success' => true,
            'error' => null,
        ]);
});

test('scan progress updated event broadcasts expected payload', function () {
    $event = new ScanProgressUpdated(
        scanId: 10,
        userId: 2,
        status: ScanStatus::Running,
        stage: 'subfinder',
        progress: 25,
        message: 'Discovering subdomains',
    );

    expect($event->broadcastAs())->toBe('scan.progress.updated')
        ->and($event->broadcastWith())->toBe([
            'scan_id' => 10,
            'status' => 'running',
            'stage' => 'subfinder',
            'progress' => 25,
            'message' => 'Discovering subdomains',
        ])
        ->and($event->broadcastOn()[0]->name)->toBe('private-scans.10');
});
