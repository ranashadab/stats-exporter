<?php

namespace Booj\StatsExporter\Tests\Commands;

use Booj\StatsExporter\Commands\StatsExportCommand;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase as ParentTestCase;

/**
 * Class CleanUpSpamAccountsCommandTest
 * @package Tests\Console\Commands
 */
class StatsExportCommandTest extends ParentTestCase
{


    /**
     * @dataProvider valid_dates_provider
     */
    public function test_get_manual_max_to_dates_should_return_array_for_valid_dates($now, $from, $to)
    {
        $command = new StatsExportCommand();
        $command->now = Carbon::parse($now);
        $manual_max_to_dates = $command->getManualMaxToDates($from, $to);
        $this->assertIsArray($manual_max_to_dates);
        $expected_size = ceil(Carbon::parse($to)->diffInMinutes($from) / StatsExportCommand::MAX_QUERY_MINUTES);
        $this->assertCount($expected_size, $manual_max_to_dates);
        foreach ($manual_max_to_dates as $manual_max_date) {
            $this->assertArrayHasKey('latest', $manual_max_date);
            $this->assertArrayHasKey('next', $manual_max_date);
            $this->assertInstanceOf('Carbon\Carbon', $manual_max_date['next']);
            $this->assertLessThanOrEqual(StatsExportCommand::MAX_QUERY_MINUTES, $manual_max_date['next']->diffInMinutes($manual_max_date['latest']));
        }
    }

    /**
     * @dataProvider partial_dates_provider
     */
    public function test_get_manual_max_to_dates_should_not_allow_partial_dates($now, $from, $to)
    {
        $command = new StatsExportCommand();
        $command->now = Carbon::parse($now);
        $this->expectException('Exception');
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Partial Time Not Allowed!');
        $command->getManualMaxToDates($from, $to);
    }

    public function valid_dates_provider()
    {
        return [
            ['2021/06/22 11:50:00', '2021/06/22 11:05:00', '2021/06/22 11:10:00'],
            ['2021/06/22 11:50:00', '2021/06/22 11:05:00', '2021/06/22 11:15:00'],
            ['2021/06/22 23:50:00', '2021/06/22 11:05:00', '2021/06/22 22:30:00']
        ];
    }

    public function partial_dates_provider()
    {
        return [
            ['2021/06/22 11:10:00', '2021/06/22 11:10:00', '2021/06/22 11:15:00'],
            ['2021/06/22 11:10:00', '2021/06/22 11:05:00', '2021/06/22 11:15:00'],
        ];
    }
}
