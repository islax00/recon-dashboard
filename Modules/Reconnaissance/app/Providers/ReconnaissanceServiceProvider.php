<?php

namespace Modules\Reconnaissance\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Reconnaissance\Models\Scan;
use Modules\Reconnaissance\Policies\ScanPolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;

class ReconnaissanceServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Reconnaissance';

    public function boot(): void
    {
        parent::boot();

        Gate::policy(Scan::class, ScanPolicy::class);
    }

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'reconnaissance';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
