<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Modules\Core\Contracts\ReconCommandRunnerInterface;
use Modules\Core\Enums\ScanStatus;
use Modules\Core\Services\FakeReconCommandRunner;
use Modules\Graph\Models\GraphNode;
use Modules\JsAnalyzer\Models\JsSecret;
use Modules\Reconnaissance\Models\Scan;
use Modules\Report\Models\Report;
use Modules\Subdomain\Models\Subdomain;

beforeEach(function () {
    $this->app->bind(ReconCommandRunnerInterface::class, FakeReconCommandRunner::class);

    Http::fake([
        'https://example.com/app.js' => Http::response('const api_key = "test-secret-key-123456789";'),
    ]);
});

test('authenticated users can create and complete a scan pipeline', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('scans.store'), [
        'domain' => 'example.com',
    ]);

    $response->assertCreated()
        ->assertJsonPath('scan.domain', 'example.com');

    $scan = Scan::query()->first();

    expect($scan)->not->toBeNull()
        ->and($scan->status)->toBe(ScanStatus::Completed)
        ->and(Subdomain::query()->where('scan_id', $scan->id)->count())->toBeGreaterThan(0)
        ->and(Report::query()->where('scan_id', $scan->id)->exists())->toBeTrue()
        ->and(GraphNode::query()->where('scan_id', $scan->id)->count())->toBeGreaterThan(0)
        ->and(JsSecret::query()->where('scan_id', $scan->id)->count())->toBeGreaterThan(0);
});

test('users cannot view scans they do not own', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $scan = Scan::factory()->for($owner)->create();

    $this->actingAs($otherUser)
        ->getJson(route('scans.show', $scan))
        ->assertForbidden();
});

test('users can fetch scan graph data', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('scans.store'), [
        'domain' => 'example.com',
    ]);

    $scan = Scan::query()->firstOrFail();

    $this->actingAs($user)
        ->getJson(route('scans.graph', $scan))
        ->assertSuccessful()
        ->assertJsonStructure([
            'graph' => [
                'nodes',
                'edges',
            ],
        ]);
});

test('users can chat about scan results', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('scans.store'), [
        'domain' => 'example.com',
    ]);

    $scan = Scan::query()->firstOrFail();

    $this->actingAs($user)
        ->postJson(route('scans.chat', $scan), [
            'message' => 'Summarize the findings.',
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['answer']);
});
