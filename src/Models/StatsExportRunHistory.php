<?php

namespace Booj\StatsExporter\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StatsExportRunHistory extends Model
{
    public static $success_status = 'success';
    public static $failure_status = 'failure';
    protected $table = 'stats_export_run_history';

    protected $fillable = [
        'job_name',
        'job_code',
        'status',
        'last_run_date',
        'max_to_date',
        'exception',
        'exporter_class'
    ];

    protected $casts = [
        'job_name' => 'string',
        'job_code' => 'string',
        'status' => 'string',
        'last_run_date' => 'datetime:Y-m-d H:i',
        'max_to_date' => 'datetime:Y-m-d H:i',
        'exporter_class' => 'string',
        'exception' => 'string'
    ];

    public function getMaxToDateAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function getLastRunDateAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function scopeLastSuccess($query)
    {
        return $query->where('status', self::$success_status)->orderBy('max_to_date', 'desc')->limit(1);
    }

    public function scopeFirstFailure($query)
    {
        return $query->where('status', self::$failure_status)->orderBy('max_to_date', 'asc')->limit(1);
    }
}
