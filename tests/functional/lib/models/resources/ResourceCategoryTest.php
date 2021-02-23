<?php


/**
 * ResourceCategoryTest.php - A test for the ResourceCategory class.
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


class ResourceCategoryTest extends \Codeception\Test\Unit
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

        //Everything is set up for the test cases.
    }

    protected function _after()
    {
        //We must roll back the changes we made in this test
        //so that the live database remains unchanged after
        //all the test cases of this test have been finished:
        $this->db_handle->rollBack();
    }

    public function testCreateResourceCategory()
    {
        $resource_cat = new ResourceCategory();
        $resource_cat->name = 'Test Category';
        $resource_cat->class_name = 'Resource';
        $resource_cat->iconnr = '1';
        $resource_cat->store();

        $this->assertEquals(
            'Test Category',
            $resource_cat->name
        );

        $this->assertEquals(
            'Resource',
            $resource_cat->class_name
        );

        $this->assertEquals(
            '1',
            $resource_cat->iconnr
        );
    }

    public function testGetClassNameById()
    {
        $resource_cat = new ResourceCategory();
        $resource_cat->name = 'Test Category';
        $resource_cat->class_name = 'Resource';
        $resource_cat->iconnr = '1';
        $resource_cat->store();

        $class_name = ResourceCategory::getClassNameById($resource_cat->id);

        $this->assertEquals(
            $class_name,
            $resource_cat->class_name
        );
    }

    public function testCreateResourceCategoryProperty()
    {
        $resource_cat = new ResourceCategory();
        $resource_cat->name = 'Test Category';
        $resource_cat->class_name = 'Resource';
        $resource_cat->iconnr = '1';
        $resource_cat->store();

        $property_name = 'test_property' . date('Ymd_His');

        $def = new ResourcePropertyDefinition();
        $def->name = $property_name;
        $def->type = 'num';
        $def->searchable = '1';
        $def->range_search = '0';
        $def->store();

        $prop = $resource_cat->addProperty(
            $property_name,
            'num',
            true,
            true
        );

        $this->assertEquals(
            $property_name,
            $prop->name
        );

        $this->assertEquals(
            'num',
            $prop->type
        );

        $this->assertEquals(
            '1',
            $prop->requestable
        );

        $this->assertEquals(
            '1',
            $prop->protected
        );

        $this->assertTrue(
            $resource_cat->hasProperty($property_name, 'num')
        );
    }

    public function testCreateInvalidResourceCategoryProperty()
    {
        $this->expectException(
            ResourcePropertyException::class
        );

        $resource_cat = new ResourceCategory();
        $resource_cat->name = 'Test Category';
        $resource_cat->class_name = 'Resource';
        $resource_cat->iconnr = '1';
        $resource_cat->store();

        $def = new ResourcePropertyDefinition();
        $def->name = 'test';
        $def->type = 'unknown';
        $def->store();

        $prop = $resource_cat->addProperty(
            'test',
            'unknown',
            true,
            true
        );
    }

    public function testGetIco()
    {
        $resource_cat = new ResourceCategory();
        $resource_cat->name = 'Test Category';
        $resource_cat->class_name = 'Resource';
        $resource_cat->iconnr = '1';
        $resource_cat->store();

        $this->assertEquals(
            (string)Icon::create('home', 'info'),
            (string)$resource_cat->getIcon()
        );
    }
}
