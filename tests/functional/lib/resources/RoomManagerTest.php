<?php


require_once __DIR__ . '/../../_bootstrap.php';
require_once 'lib/resources/RoomManager.class.php';
require_once 'lib/resources/ResourceManager.class.php';


class RoomManagerTest extends \Codeception\Test\Unit
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

        $this->user = new User();
        $this->user->username = 'test_user_' . date('YmdHis');
        $this->user->vorname = 'Test';
        $this->user->nachname = 'User';
        $this->user->perms = 'admin';
        $this->user->store();

        ResourceManager::setGlobalResourcePermission($this->user, 'admin');

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

        $this->room->seats = 25;

        $this->room->createLock(
            $this->user,
            new DateTime('2017-10-02 8:00:00 +0000'),
            new DateTime('2017-10-02 18:00:00 +0000')
        );

        $this->course = new Course();
        $this->course->name = 'test_course_' . date('YmdHis');
        $this->course->store();

        $this->course_date = new CourseDate();
        $this->course_date->range_id = $this->course->id;
        $this->course_date->autor_id = $this->user->id;
        $this->course_date->date = strtotime('2017-10-01 8:00:00 +0000');
        $this->course_date->end_time = strtotime('2017-10-01 10:00:00 +0000');
        $this->course_date->store();
    }

    protected function _after()
    {
        //We must roll back the changes we made in this test
        //so that the live database remains unchanged after
        //all the test cases of this test have been finished:
        $this->db_handle->rollBack();
    }
}
