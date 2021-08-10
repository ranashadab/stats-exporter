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

    public function __construct(Carbon $from, Carbon $to)
    {
        $this->from = $from->second(0);
        $this->to = $to->second(0);
    }

    abstract public function query(): Builder;

    public function export($disk, $filename) : void
    {
        $path = env('ENVIRONMENT', 'dev') . "/notifications/";
        $file_contents = $this->query()->get();
        Storage::disk($disk)->put($path . $filename, json_encode($file_contents));
    }

    public function getTo(): Carbon
    {
        return $this->to;
    }
}
