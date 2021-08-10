<?php

namespace Booj\StatsExporter\Interfaces;

use Illuminate\Database\Query\Builder;

interface JsonExporterInterface
{
    public function query(): Builder;
    public function export(string $disk, string $filename): void;
}
