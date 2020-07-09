<?php

/**
 * BuildingTest.php - A test for the Building class.
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

class BuildingTest extends \Codeception\Test\Unit
{
    protected $db_handle;
    protected $oldUser;

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function _before()
    {
        //First we must initialise the StudipPDO database connection:
        $this->db_handle = new \StudipPDO(
            'mysql:host=' . $GLOBALS['DB_STUDIP_HOST'] . ';dbname=' . $GLOBALS['DB_STUDIP_DATABASE'],
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
            \User::build(['user_id' => 'cli', 'username' => 'cli', 'perms' => 'autor'], false)
        );

        //As a final step we create the SORM objects for our test cases:

        $this->location_cat = ResourceManager::createLocationCategory('TestLocation');
        $this->building_cat = ResourceManager::createBuildingCategory('TestBuilding');

        $this->location = $this->location_cat->createResource('Test location object');

        $this->building = $this->building_cat->createResource('Test building object', '', $this->location->id);

        //Everything is set up for the test cases.
    }

    protected function _after()
    {
        //We must roll back the changes we made in this test
        //so that the live database remains unchanged after
        //all the test cases of this test have been finished:
        $this->db_handle->rollBack();

        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $GLOBALS['user'] = $this->oldUser;
    }

    public function testValidation()
    {
        $this->expectException(InvalidResourceException::class);

        $invalid = new Building();
        $invalid->name = 'invalid';
        //The building is invalid since it doesn't have a location
        //as a parent resource.
        $invalid->store();
    }

    public function testGetURL()
    {
        $link = $this->building->getActionURL('show');

        $this->assertEquals('dispatch.php/resources/building/index/' . $this->building->id, $link);

        $link = $this->building->getActionURL('show', ['test' => '1']);

        $this->assertEquals('dispatch.php/resources/building/index/' . $this->building->id . '?test=1', $link);

        $link = $this->building->getActionURL('delete');

        $this->assertEquals('dispatch.php/resources/building/delete/' . $this->building->id, $link);
    }
}
