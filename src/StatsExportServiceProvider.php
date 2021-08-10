<?php

namespace Booj\StatsExporter\Providers;

use Booj\StatsExporter\Commands\StatsExportCommand;
use Illuminate\Support\ServiceProvider;

class StatsExporterServiceProvider extends ServiceProvider
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
            __DIR__.'/config/stats_exporter.php', 'stats_exporter'
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
    }
}
