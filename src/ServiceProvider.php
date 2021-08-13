<?php

namespace Booj\StatsExporter;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Booj\StatsExporter\Commands\StatsExportCommand;
use Illuminate\Console\Scheduling\Schedule;

class ServiceProvider extends BaseServiceProvider
{
    protected $commands = [
        StatsExportCommand::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/stats_exporter.php',
            'stats_exporter'
        );
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/stats_exporter.php' => config_path('stats_exporter.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }


        // Task Scheduling can be considered here
        // FOR LUMEN:
        // IMP: Bad Practice?
        // $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
        //         // schedule here
        // });

        // FOR LARAVEL:
        // IMP: Bad Practice?
        // $this->app->booted(function () {
        //     $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
        //         // schedule here
        //     });
        // });
    }
}
