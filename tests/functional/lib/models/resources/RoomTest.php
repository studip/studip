<?php


/**
 * RoomTest.php - A test for the Room class.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     tests
 * @since       4.2
 */


require_once __DIR__ . '/../../../_bootstrap.php';


class RoomTest extends \Codeception\Test\Unit
{
    protected $db_handle;
    protected $oldPerm, $oldUser;

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

        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $this->oldUser = $GLOBALS['user'];
        $GLOBALS['user'] = new \Seminar_User(
            \User::build(['user_id' => 'cli', 'username' => 'cli', 'perms' => 'root'], false)
        );
        $this->oldPerm = $GLOBALS['perm'];
        $GLOBALS['perm'] = new \Seminar_Perm();

        //As a final step we create the SORM objects for our test cases:

        $this->test_user_username = 'test_user_' . date('YmdHis');
        $this->test_user = new User();
        $this->test_user->username = $this->test_user_username;
        $this->test_user->vorname = 'Test';
        $this->test_user->nachname = 'User';
        $this->test_user->perms = 'admin';
        $this->test_user->store();

        $perms = new ResourcePermission();
        $perms->user_id = $this->test_user->id;
        $perms->resource_id = 'global';
        $perms->perms = 'tutor';
        $perms->store();

        $this->location_cat = ResourceManager::createLocationCategory(
            'TestLocation'
        );
        $this->building_cat = ResourceManager::createBuildingCategory(
            'TestBuilding'
        );
        $this->room_cat = ResourceManager::createRoomCategory(
            'TestRoom'
        );

        $this->location = $this->location_cat->createResource(
            'Test location object'
        );

        $this->building = $this->building_cat->createResource(
            'Test building object',
            '',
            $this->location->id
        );

        $this->room = $this->room_cat->createResource(
            'Test room object',
            '',
            $this->building->id
        );

        //The room must have at least than 22 seats or we won't find
        //it when we're testing finding rooms by a room request.
        //The request we create is for a room with at least 22 seats.
        $this->room->seats = 25;

        $this->room->requestable = '1';

        $this->room->store();

        $this->request_begin_date = new DateTime();
        $this->request_begin_date->setTime(12,0,0);
        $this->request_end_date = clone $this->request_begin_date;
        $this->request_end_date->setTime(14,0,0);
        $this->course_date = new CourseDate();
        $this->course_date->date = $this->request_begin_date->getTimestamp();
        $this->course_date->end_time = $this->request_end_date->getTimestamp();
        $this->course_date->store();

        $this->room_request = $this->room->createRequest(
            $this->test_user,
            $this->course_date->id,
            'test',
            [
                'seats' => 22
            ]
        );

        //Everything is set up for the test cases.
    }

    protected function _after()
    {
        //We must roll back the changes we made in this test
        //so that the live database remains unchanged after
        //all the test cases of this test have been finished:
        $this->db_handle->rollBack();

        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $GLOBALS['perm'] = $this->oldPerm;
        $GLOBALS['user'] = $this->oldUser;
    }

    //The tests for findParentByClassName and findChildrenByClassName
    //could have been placed in the ResourceTest class. But since
    //we must create a resource hierarchy in the RoomTest class
    //anyways to test the Room class, we can also test those methods
    //in here.

    public function testFindParentByClassName()
    {
        $location = $this->room->findParentByClassName('Location');

        $this->assertEquals(
            $this->location->name,
            $location->name
        );

        $this->assertEquals(
            $this->room->parent->parent->id,
            $location->id
        );
    }

    public function testFindChildrenByClassName()
    {
        $room = $this->location->findChildrenByClassName('Room')[0];

        $this->assertEquals(
            $this->room->name,
            $room->name
        );

        //The location and the building each have one child.
        $this->assertEquals(
            $room->id,
            $this->location->children[0]->children[0]->id
        );
    }

    public function testFindBuilding()
    {
        $building = $this->room->findBuilding();

        $this->assertEquals(
            $building->id,
            $this->building->id
        );
    }

    public function testFindByBuilding()
    {
        $rooms = Room::findByBuilding($this->building->id);

        $this->assertEquals(
            $rooms[0]->id,
            $this->room->id
        );

        $this->assertEquals(
            $rooms[0]->name,
            $this->room->name
        );
    }

    public function testFindByRoomRequestAndProperties()
    {
        $room = Room::findByRoomRequestAndProperties(
            $this->room_request,
            $this->test_user,
            0,
            0,
            [$this->room]
        )[0];

        $this->assertEquals(
            $this->room->id,
            $room->id
        );
    }
}
