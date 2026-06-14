<?php

use App\Models\User;
use Modules\Core\Contracts\ReconCommandRunnerInterface;
use Modules\Core\Services\FakeReconCommandRunner;
use Modules\Reconnaissance\Models\Scan;

beforeEach(function () {
    $this->app->bind(ReconCommandRunnerInterface::class, FakeReconCommandRunner::class);
});

test('users can view scans index page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('scans.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('scans/Index')
            ->has('scans'));
});

test('users can view scan show page', function () {
    $user = User::factory()->create();
    $scan = Scan::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('scans.show', $scan))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('scans/Show')
            ->has('scan')
            ->has('graph')
            ->has('stats'));
});

test('creating a scan via form redirects to show page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('scans.store'), [
            'domain' => 'example.com',
        ])
        ->assertRedirect(route('scans.show', Scan::query()->first()));
});
