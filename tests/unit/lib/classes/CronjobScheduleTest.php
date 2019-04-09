<?php
/*
 * Copyright (C) 2013 - Jan-Hendrik Willms <tleilax+studip@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once 'lib/models/SimpleORMap.class.php';
require_once 'lib/classes/Config.class.php';
require_once 'lib/classes/DBManager.class.php';
require_once 'lib/classes/StudipCache.class.php';
require_once 'lib/classes/StudipCacheFactory.class.php';
require_once 'lib/models/StudipCacheOperation.php';
require_once 'lib/models/CronjobSchedule.class.php';

class CronjobTestSchedule extends SimpleORMap
{
}

class ScheduleTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        date_default_timezone_set('Europe/Berlin');

        StudipTestHelper::set_up_tables(['cronjobs_schedules']);
    }

    function tearDown()
    {
        StudipTestHelper::tear_down_tables();
    }

    function testOnceSchedule()
    {
        $schedule = new CronjobSchedule();
        $schedule->type = 'once';

        $this->assertEquals('once', $schedule->type);

        return $schedule;
    }

    /**
     * @depends testOnceSchedule
     */
    function testNextExecutionOncePast($schedule)
    {
        $now  = strtotime('10.11.2013 01:02:00');
        $then = strtotime('-2 weeks', $now);

        $schedule->next_execution = $then;
        $schedule->calculateNextExecution();

        $this->assertEquals($then, $schedule->next_execution);
    }

    /**
     * @depends testOnceSchedule
     */
    function testNextExecutionOncePresent($schedule)
    {
        $now = strtotime('10.11.2013 01:02:00');

        $schedule->next_execution = $now;
        $schedule->calculateNextExecution();

        $this->assertEquals($now, $schedule->next_execution);
    }

    /**
     * @depends testOnceSchedule
     */
    function testNextExecutionOnceFuture($schedule)
    {
        $now  = strtotime('10.11.2013 01:02:00');
        $then = strtotime('+2 weeks', $now);

        $schedule->next_execution = $next_execution;
        $schedule->calculateNextExecution();

        $this->assertEquals($next_execution, $schedule->next_execution);
    }

    function testPeriodicSchedule()
    {
        $schedule = new CronjobSchedule();
        $schedule->type        = 'periodic';
        $schedule->minute      = null;
        $schedule->hour        = null;
        $schedule->day         = null;
        $schedule->month       = null;
        $schedule->day_of_week = null;

        $this->assertEquals('periodic', $schedule->type);

        return $schedule;
    }

    /**
     * @depends testPeriodicSchedule
     */
    function testNextExecutionPeriodicMinutely($schedule)
    {
        $now  = strtotime('10.11.2013 01:02:00');
        $then = strtotime('10.11.2013 01:03:00');
        $schedule->calculateNextExecution($now);

        $this->assertEquals($then, $schedule->next_execution);
    }

    /**
     * @depends testPeriodicSchedule
     */
    function testNextExecutionPeriodicHourly($schedule)
    {
        $now  = strtotime('10.11.2013 01:02:00');
        $then = strtotime('10.11.2013 02:00:00');

        $schedule->minute      = 0;
        $schedule->hour        = null;
        $schedule->day         = null;
        $schedule->month       = null;
        $schedule->day_of_week = null;
        $schedule->calculateNextExecution($now);

        $this->assertEquals($then, $schedule->next_execution);
    }

    /**
     * @depends testPeriodicSchedule
     */
    function testNextExecutionPeriodicDaily($schedule)
    {
        $now  = strtotime('10.11.2013 01:02:00');
        $then = strtotime('11.11.2013 00:00:00');

        $schedule->minute      = 0;
        $schedule->hour        = 0;
        $schedule->day         = null;
        $schedule->month       = null;
        $schedule->day_of_week = null;
        $schedule->calculateNextExecution($now);

        $this->assertEquals($then, $schedule->next_execution);
    }

    /**
     * @depends testPeriodicSchedule
     */
    function testNextExecutionPeriodicMonthly($schedule)
    {
        $now  = strtotime('10.11.2013 01:02:00');
        $then = strtotime('01.12.2013 00:00:00');

        $schedule->minute      = 0;
        $schedule->hour        = 0;
        $schedule->day         = 1;
        $schedule->month       = null;
        $schedule->day_of_week = null;
        $schedule->calculateNextExecution($now);

        $this->assertEquals($then, $schedule->next_execution);
    }

    /*
     * @depends testPeriodicSchedule
    function testNextExecutionPeriodicYearly($schedule)
    {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete('This section needs to be optimized so this test is skipped.');

        $now  = strtotime('01.01.2013 00:01:00');
        $then = strtotime('01.01.2014 00:00:00');

        $schedule->minute      = 0;
        $schedule->hour        = 0;
        $schedule->day         = 1;
        $schedule->month       = 1;
        $schedule->day_of_week = null;
        $schedule->calculateNextExecution($now);

        $this->assertEquals($then, $schedule->next_execution);
    }
    */

    /**
     * @depends testPeriodicSchedule
     */
    function testNextExecutionPeriodicFriday($schedule)
    {
        $now  = strtotime('10.11.2013 01:02:00');
        $then = strtotime('next friday 0:00:00', $now);

        $schedule->minute      = null;
        $schedule->hour        = null;
        $schedule->day         = null;
        $schedule->month       = null;
        $schedule->day_of_week = 5;
        $schedule->calculateNextExecution($now);

        $this->assertEquals($then, $schedule->next_execution);
    }

    /**
     * @depends testPeriodicSchedule
     */
    function testBuggyConditions($schedule)
    {
        $now  = strtotime('16.04.2013 01:10:00');
        $then = strtotime('17.04.2013 01:07:00');

        $schedule->minute      = 7;
        $schedule->hour        = 1;
        $schedule->day         = null;
        $schedule->month       = null;
        $schedule->day_of_week = null;
        $schedule->calculateNextExecution($now);

        $this->assertEquals($then, $schedule->next_execution);
    }
}
