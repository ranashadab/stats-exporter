<?php

namespace Booj\StatsExporter\StatsQueries;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Query\Builder;
use Booj\StatsExporter\Interfaces\JsonExporterInterface;
use Carbon\Carbon;

abstract class StatQueryExporter implements JsonExporterInterface
{
    protected $from;
    protected $to;

    /**
    * Construct with Carbon Dates.
    * @param Carbon $from
    * @param Carbon $to
    */
    public function __construct(Carbon $from, Carbon $to)
    {
        $this->from = $from->second(0);
        $this->to = $to->second(0);
    }

    /**
    * @return Builder
    */
    abstract public function query(): Builder;

    /**
    * Exports file with provided name on required disk.
    * @param string $disk
    * @param string $filename
    * @return void
    */
    public function export($disk, $filename) : void
    {
        $path = env('ENVIRONMENT', 'dev') . "/notifications/";
        $file_contents = $this->query()->get();
        Storage::disk($disk)->put($path . $filename, json_encode($file_contents));
    }

    /**
    * Returns $to Date
    * @return Carbon
    */
    public function getTo(): Carbon
    {
        return $this->to;
    }
}
