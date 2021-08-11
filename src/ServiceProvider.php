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

        //IMP: Bad Practice? 
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $exporter_classes = config('stats_exporters.exporter_classes');
            if ($exporter_classes && is_array($exporter_classes)) {
                foreach ($exporter_classes as $exporter_class) {
                    $schedule->command("stats:export '{$exporter_class}'")->everyFiveMinutes();
                }
            }
        });
    }
}
