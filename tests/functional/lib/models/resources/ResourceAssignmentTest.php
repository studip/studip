<?php


/**
 * ResourceBookingTest.php - A test for the resource booking functionality.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2020
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     tests
 * @since       4.5
 */


require_once __DIR__ . '/../../../_bootstrap.php';


class ResourceBookingTest extends \Codeception\Test\Unit
{
    protected $db_handle;

    protected function _before()
    {
        //First we must initialise the StudipPDO database connection:
        $this->db_handle = new \StudipPDO(
            'mysql:host='
                . $GLOBALS['DB_STUDIP_HOST']
                . ';dbname='
                . $GLOBALS['DB_STUDIP_DATABASE'],
            $GLOBALS['DB_STUDIP_USER'],
            $GLOBALS['DB_STUDIP_PASSWORD']
        );

        //Then we must start a transaction before we access the database,
        //otherwise we would spam the live database with test data!
        $this->db_handle->beginTransaction();

        //Now we tell the DBManager about the connection
        //we have established to the Stud.IP database:
        \DBManager::getInstance()->setConnection('studip', $this->db_handle);

        //As a final step we create the SORM objects for our test cases:

        $this->test_user_username = 'test_user_' . date('YmdHis');
        $this->test_user = new User();
        $this->test_user->username = $this->test_user_username;
        $this->test_user->vorname = 'Test';
        $this->test_user->nachname = 'User';
        $this->test_user->perms = 'admin';
        $this->test_user->store();

        $perm = new ResourcePermission();
        $perm->user_id = $this->test_user->id;
        $perm->resource_id = 'global';
        $perm->perms = 'admin';
        $perm->store();

        $this->resource_category = ResourceManager::createCategory(
            'Test'
        );
        $this->resource = $this->resource_category->createResource(
            'Test Resource' . date('Ymd')
        );

        $this->booking = $this->resource->createBooking(
            $this->test_user,
            $this->test_user->id,
            new DateTime('2017-12-01 8:00:00+0000'),
            new DateTime('2017-12-01 14:00:00+0000'),
            new DateTime('2017-12-05 23:59:59+0000'),
            4,
            1,
            'd'
        );

        $this->another_booking = $this->resource->createBooking(
            $this->test_user,
            $this->test_user->id,
            new DateTime('2017-12-06 0:00:00+0000'),
            new DateTime('2017-12-06 12:00:00+0000'),
            new DateTime('2017-12-08 23:59:59+0000'),
            2,
            1,
            'd'
        );
        //Everything is set up for the test cases.
    }

    protected function _after()
    {
        //We must roll back the changes we made in this test
        //so that the live database remains unchanged after
        //all the test cases of this test have been finished:
        $this->db_handle->rollBack();
    }

    public function testEndWithSemester()
    {
        $this->assertFalse(
            $this->booking->endsWithSemester()
        );
    }

    public function testIsRepetitionInTimeframe()
    {
        $begin = new DateTime('2017-12-02 0:00:00+0000');
        $end = new DateTime('2017-12-02 23:59:59+0000');

        $this->assertTrue(
            $this->booking->isRepetitionInTimeframe($begin, $end)
        );
    }

    public function testGetRepetitionInterval()
    {
        $interval = new DateInterval('P1D');

        $this->assertEquals(
            $interval,
            $this->booking->getRepetitionInterval()
        );
    }

    public function testGetRepeatModeString()
    {
        $this->assertEquals(
            _('jeden Tag'),
            $this->booking->getRepeatModeString()
        );
    }

    public function testHasOverlappingBookings()
    {
        $this->assertFalse(
            $this->booking->hasOverlappingBookings()
        );
    }

    public function testGetOverlappingBookings()
    {
        $found_overlaps = $this->booking->getOverlappingBookings();

        $this->assertEquals(
            0,
            count($found_overlaps[0])
        );
    }

    public function testGetTimeIntervals()
    {
        $time_intervals = $this->booking->getTimeIntervals();

        $this->assertEquals(
            5,
            count($time_intervals)
        );
    }
}
