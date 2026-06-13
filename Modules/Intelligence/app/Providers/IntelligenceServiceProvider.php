<?php

namespace Modules\Intelligence\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Nwidart\Modules\Support\ModuleServiceProvider;

class IntelligenceServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Intelligence';

    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(module_path($this->name, 'config/intelligence.php'), 'intelligence');
    }

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'intelligence';

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
