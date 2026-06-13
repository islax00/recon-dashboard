<?php

namespace Modules\Core\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Core\Contracts\ReconCommandRunnerInterface;
use Modules\Core\Services\ReconCommandRunner;
use Nwidart\Modules\Support\ModuleServiceProvider;

class CoreServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Core';

    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(module_path($this->name, 'config/recon.php'), 'recon');

        $this->app->bind(
            ReconCommandRunnerInterface::class,
            ReconCommandRunner::class,
        );
    }

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'core';

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
