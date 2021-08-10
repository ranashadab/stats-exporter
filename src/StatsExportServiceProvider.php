<?php

namespace Booj\StatsExporter\Providers;

use Booj\StatsExporter\Commands\StatsExportCommand;
use Illuminate\Support\ServiceProvider;

class StatsExportServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                StatsExportCommand::class,
            ]);
        }
    }
}
