<?php

namespace Booj\StatsExporter\Commands;

use Booj\StatsExporter\Jobs\StatExportJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Booj\StatsExporter\Models\StatsExportRunHistory;
use Illuminate\Support\Str;

class StatsExportCommand extends Command
{
    public const DEFAULT_EXTENSION = 'json';
    public const STATS_EXPORT_STORAGE = "s3_stats_export";
    public const MAX_QUERY_MINUTES = 360;
    public $name;
    public $now;
    public $from;
    public $to;

    //Sample manual run
    //php artisan stats:export 'App\Exports\StatsQueries\TokenBlacklist' '2021/06/22 11:05:00' '2021/06/22 11:30:00'
    public $signature = 'stats:export {name? : Exporter Class} {from? : From DateTime (possible valid format : 2021/06/22 11:05:00) } {to? : To DateTime (possible valid format : 2021/06/22 11:30:00)} {--run-for-all} ';
    public $description = 'Export stats data for Datadog.';

    public function handle()
    {
        $this->comment('Exporting Stats...');

        try {
            $this->name = $this->argument('name');
            $this->from = $this->argument('from');
            $this->to = $this->argument('to');
            $run_for_all = $this->option('run-for-all');

            $exporters = config('stats_exporter.exporter_classes');
            $this->now = Carbon::now();
            
            if ($run_for_all) {
                foreach ($exporters as $exporter) {
                    $this->name = $exporter;
                    $this->dispatchJobs();
                }
                return;
            }

            if (!$exporters or !in_array($this->name, $exporters)) {
                throw new \Exception('Exporter not Found!', 404);
                return;
            }
            $this->dispatchJobs();
        } catch (\Exception $exception) {
            Log::info($exception->getMessage() . " " . $exception->getTraceAsString());
            $this->error("Aborting.");
        }
    }

    /**
    * @param string $from
    * @param string $to
    * @return array
    */
    public function getManualMaxToDates($from, $to): array
    {
        $from = Carbon::parse($from);
        $to = Carbon::parse($to);
        if ($to > $this->now) {
            throw new \Exception('Partial Time Not Allowed!', 403);
        }

        $gap = $from->diffInMinutes($to);

        $max_to_dates = [];
        while ($gap) {
            $diff = $gap >= self::MAX_QUERY_MINUTES ? self::MAX_QUERY_MINUTES : $gap;
            $max_to_dates[] = ['latest' => $from, 'next' => $from->copy()->addMinutes($diff)];
            $from = $from->copy()->addMinutes($diff);
            $gap -= $diff;
        }
        return $max_to_dates;
    }

    /**
    * @return array
    */
    public function getRegularMaxToDates(): array
    {
        $query = StatsExportRunHistory::where('exporter_class', $this->name);
        $latest_max_to_date = with(clone $query)->lastSuccess()->first()->max_to_date ??
            $query->firstFailure()->first()->max_to_date ??
            $this->now->copy()->subMinutes(5);

        if (!$latest_max_to_date instanceof Carbon) {
            $latest_max_to_date = Carbon::parse($latest_max_to_date);
        }

        $gap = $latest_max_to_date->diffInMinutes($this->now);

        $max_to_dates = [];
        while ($gap) {
            $diff = $gap >= self::MAX_QUERY_MINUTES ? self::MAX_QUERY_MINUTES : $gap;
            $max_to_dates[] = ['latest' => $latest_max_to_date, 'next' => $latest_max_to_date->copy()->addMinutes($diff)];
            $latest_max_to_date = $latest_max_to_date->copy()->addMinutes($diff);
            $gap -= $diff;
        }
        return $max_to_dates;
    }

    /** 
    * @return array
    */
    public function getMaxToDates(): array
    {
        if ($this->from && $this->to) {
            return $this->getManualMaxToDates($this->from, $this->to);
        }

        return $this->getRegularMaxToDates();
    }

    /** 
    * @return void
    */
    public function dispatchJobs(): void
    {
        $max_to_dates = $this->getMaxToDates();
        foreach ($max_to_dates as $max_to_date) {
            $temp_arr = explode('\\', $this->name);
            $job_code = Str::kebab(end($temp_arr));
            $filename = "stats-export-{$job_code}-{$max_to_date['next']->toJson()}." . self::DEFAULT_EXTENSION;
            dispatch_now(new StatExportJob(
                new $this->name($max_to_date['latest'], $max_to_date['next']),
                $job_code,
                $filename,
                self::STATS_EXPORT_STORAGE
            ));
        }
    }
}
