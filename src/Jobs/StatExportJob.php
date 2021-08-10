<?php

namespace Booj\StatsExporter\Jobs;

use Booj\StatsExporter\Interfaces\JsonExporterInterface;
use App\Models\Stats\StatsExportRunHistory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class StatExportJob extends Job
{
    protected string $filename;
    protected string $job_code;
    protected string $disk;
    protected JsonExporterInterface $exporter;

    public function __construct(JsonExporterInterface $exporter, string $job_code, string $filename, string $disk = 'local')
    {
        $this->exporter = $exporter;
        $this->job_code = $job_code;
        $this->filename = $filename;
        $this->disk = $disk;
        $this->now = Carbon::parse(null, 'UTC');
        $this->job_name = Str::kebab((new \ReflectionClass($this->exporter))->getShortName()) . "-" . $this->now;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $history = [
                'last_run_date' => $this->now,
                'max_to_date' => $this->exporter->getTo(),
                'job_name' => $this->job_name,
                'job_code' => $this->job_code,
                'exporter_class' => get_class($this->exporter),
            ];

            $this->exporter->export($this->disk, $this->filename);
            $history['status'] = StatsExportRunHistory::$success_status;
        } catch (\Exception $e) {
            $history['status'] = StatsExportRunHistory::$failure_status;
            $history['exception'] = $e->getTraceAsString();
        } finally {
            StatsExportRunHistory::create($history);
        }
    }
}
