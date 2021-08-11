<?php

namespace Tests\Unit\App\Exports;

use PHPUnit\Framework\TestCase as ParentTestCase;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class StatsQueryExportersTest extends ParentTestCase
{
    /**
     * @test
     * @dataProvider testStatsQueryDataProvider
     */
    public function test_that_it_exports_a_json_file_to_specified_disk(string $exporter, Carbon $from, Carbon $to)
    {
        $exporter = new $exporter($from, $to);
        Storage::fake('test-disk');
        $exporter->export("test-disk", "test_sample.json");
        $path = env('ENVIRONMENT', 'dev') . "/notifications/";
        Storage::disk('test-disk')->assertExists($path . 'test_sample.json');
    }

    /**
     * @test
     * @dataProvider testStatsQueryDataProvider
     */
    public function test_that_it_implements_getTo_method(string $exporter, Carbon $from, Carbon $to)
    {
        $exporter = new $exporter($from, $to);
        $this->assertTrue(method_exists($exporter, "getTo"), "getTo Implementation not found.");
    }

    /**
     * @test
     * @dataProvider testStatsQueryDataProvider
     */
    public function test_that_its_query_method_returns_builder_instance(string $exporter, Carbon $from, Carbon $to)
    {
        $exporter = new $exporter($from, $to);
        $this->assertInstanceOf(Builder::class, $exporter->query(), "Invalid response type from Query. Must return Query Builder Instance.");
    }

    public function testStatsQueryDataProvider()
    {
        $now = Carbon::now();
        // Test for all classes extending StatQueryExporter.
        return [
            // Sample Data
            // [FailedJobException::class, $now->copy(), $now->copy()->subMinutes(10)],
            // [RawFailedJob::class, $now->copy(), $now->copy()->subMinutes(15)],
            // [UsersGroupBy::class, $now->copy(), $now->copy()->subMinutes(20)]
        ];
    }
}
