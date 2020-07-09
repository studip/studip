<?php

class ResourceTest extends \Codeception\Test\Unit
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

        $this->test_property_name = 'test_' . date('YmdHis');
        $this->test_property2_name = 'some_test_user_' . date('YmdHis');

        $this->resource_cat = new ResourceCategory();
        $this->resource_cat->name = 'Test Category';
        $this->resource_cat->class_name = 'Resource';
        $this->resource_cat->iconnr = '1';
        $this->resource_cat->store();

        $def = new ResourcePropertyDefinition();
        $def->name = $this->test_property_name;
        $def->type = 'text';
        $def->searchable = '0';
        $def->range_search = '0';
        $def->store();

        $def2 = new ResourcePropertyDefinition();
        $def2->name = $this->test_property2_name;
        $def2->type = 'user';
        $def2->searchable = '0';
        $def2->range_search = '0';
        $def2->store();

        $this->test_property = $this->resource_cat->addProperty(
            $this->test_property_name,
            'text',
            true,
            true
        );

        $this->test_property2 = $this->resource_cat->addProperty(
            $this->test_property2_name,
            'user',
            false,
            true
        );

        $this->test_resource_name = 'Test Resource ' . date('YmdHis');

        $this->resource = $this->resource_cat->createResource(
            $this->test_resource_name,
            'Resource Description 20171013'
        );
        $this->resource->requestable = '1';
        $this->resource->store();

        $this->resource->setProperty(
            $this->test_property_name,
            'test'
        );

        $this->resource->setPropertyRelatedObject(
            $this->test_property2_name,
            $this->test_user
        );

        $permission = new ResourcePermission();
        $permission->user_id = $this->test_user->id;
        $permission->resource_id = $this->resource->id;
        $permission->perms = 'admin';
        $permission->store();

        $this->booking_start_time = new DateTime('2017-10-02 8:00:00 +0000');
        $this->booking_end_time = new DateTime('2017-10-02 10:00:00 +0000');
        $this->booking_repeat_end = new DateTime('2017-10-06 10:00:00 +0000');

        $this->booking = $this->resource->createBooking(
            $this->test_user,
            $this->test_user->id,
            [
                [
                    'begin' => $this->booking_start_time,
                    'end' => $this->booking_end_time
                ]
            ],
            new DateInterval('P2D'),
            2, //Shall not be regarded since a repetition end date is set.
            $this->booking_repeat_end
        );

        $this->lock_start_time = new DateTime('2017-11-01 0:00:00 +0000');
        $this->lock_end_time = new DateTime('2017-11-02 23:59:59 +0000');

        $this->lock = $this->resource->createLock(
            $this->test_user,
            $this->lock_start_time,
            $this->lock_end_time
        );

        $this->course = new Course();
        $this->course->name = 'Test Resource Course ' . date('YmdHis');
        $this->course->store();

        $this->course_date = new CourseDate();
        $this->course_date->range_id = $this->course->id;
        $this->course_date->autor_id = $this->test_user->id;
        $this->course_date->date = strtotime('2017-10-02 11:00:00 +0000');
        $this->course_date->end_time = strtotime('2017-10-02 12:00:00 +0000');
        $this->course_date->store();

        $this->request = $this->resource->createRequest(
            $this->test_user,
            $this->course->id,
            'test createRequest',
            [
                $this->test_property_name => 'test'
            ]
        );

        $this->user2 = new User();
        $this->user2->username = 'test_user2_' . date('YmdHis');
        $this->user2->perms = 'tutor';
        $this->user2->vorname = 'Test';
        $this->user2->nachname = 'User 2';
        $this->user2->store();

        $this->resource->setUserPermission(
            $this->user2,
            'admin'
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

    public function testCreateResourceWithoutCategory()
    {
        $this->expectException(
            InvalidResourceException::class
        );

        $r = new Resource();
        $r->name = 'Invalid Resource';
        $r->store();
    }

    public function testCreateResource()
    {
        //$this->resource has been created in the _before() method
        $this->assertEquals(
            $this->test_resource_name,
            $this->resource->name
        );

        $this->assertEquals(
            'Resource Description 20171013',
            $this->resource->description
        );
    }

    public function testCreateBooking()
    {
        //The booking has been created in the _before() method.

        $this->assertEquals(
            $this->resource->id,
            $this->booking->resource_id
        );

        $this->assertEquals(
            $this->test_user->id,
            $this->booking->range_id
        );

        $this->assertEquals(
            $this->booking_start_time->getTimestamp(),
            $this->booking->begin
        );

        $this->assertEquals(
            $this->booking_end_time->getTimestamp(),
            $this->booking->end
        );

        $this->assertEquals(
            $this->booking_repeat_end->getTimestamp(),
            $this->booking->repeat_end
        );

        $this->assertEquals(
            null,
            $this->booking->repeat_quantity
        );

        $this->assertEquals(
            'P00Y00M02D',
            $this->booking->repetition_interval
        );
    }

    public function testCreateLock()
    {
        //The resource lock has been created in the _before() method.

        $this->assertEquals(
            $this->resource->id,
            $this->lock->resource_id
        );

        $this->assertEquals(
            $this->lock_start_time->getTimestamp(),
            $this->lock->begin
        );

        $this->assertEquals(
            $this->lock_end_time->getTimestamp(),
            $this->lock->end
        );
    }

    public function testPropertyExists()
    {
        //The property has been created in the _before() method.

        $this->assertTrue(
            $this->resource->propertyExists(
                $this->test_property_name
            )
        );
    }

    public function testGetPropertyObject()
    {
        //The property has been created in the _before() method.

        $property = $this->resource->getPropertyObject(
            $this->test_property_name
        );

        $this->assertEquals(
            $this->test_property_name,
            $property->name
        );

        $this->assertEquals(
            'test',
            $property->state
        );
    }

    public function testGetProperty()
    {
        //The property has been created in the _before() method.

        $state = $this->resource->getProperty(
            $this->test_property_name
        );

        $this->assertEquals(
            'test',
            $state
        );
    }

    public function testNonexistingProperty()
    {
        //The resource has been created in the _before() method.

        $this->assertFalse(
            $this->resource->propertyExists(
                'nonexistant_property_' . date('YmdHis')
            )
        );
    }

    public function testGetNonexistingPropertyObject()
    {
        //The resource has been created in the _before() method.

        $no_object = $this->resource->getPropertyObject(
            'nonexistant_property_' . date('YmdHis')
        );

        $this->assertNull(
            $no_object
        );
    }

    public function testGetNonexistingProperty()
    {
        //The resource has been created in the _before() method.

        $no_object = $this->resource->getProperty(
            'nonexistant_property_' . date('YmdHis')
        );

        $this->assertNull(
            $no_object
        );
    }

    public function testGetPropertyObjectWithoutName()
    {
        $this->expectException(TypeError::class);

        //The resource has been created in the _before() method.

        $no_object = $this->resource->getPropertyObject();

        $this->assertEquals(
            null,
            $no_object
        );
    }

    public function testGetPropertyWithoutName()
    {
        $this->expectException(TypeError::class);
        //The resource has been created in the _before() method.

        $this->assertEquals(
            null,
            $this->resource->getProperty()
        );
    }

    public function testGetPropertyRelatedObject()
    {
        $user = $this->resource->getPropertyRelatedObject(
            $this->test_property2_name
        );

        $this->assertEquals(
            true,
            ($user instanceof User)
        );

        $this->assertEquals(
            $this->test_user_username,
            $user->username
        );
    }

    public function testGetNonexistantPropertyRelatedObject()
    {
        $no_object = $this->resource->getPropertyRelatedObject(
            'nonexistant_property' . date('YmdHis')
        );

        $this->assertNull(
            $no_object
        );
    }

    //Resource::setProperty and Resource::setPropertyRelatedObject
    //don't need to be tested since they are called in the _before() method.
    //If those set methods wouldn't work then the tests where
    //properties or property objects are retrieved would fail.

    public function testGetPropertyArray()
    {
        $properties = $this->resource->getPropertyArray();

        $this->assertEquals(
            2,
            count($properties)
        );
    }

    public function testGetOnlyRequestablePropertyArray()
    {
        $properties = $this->resource->getPropertyArray(true);

        $this->assertEquals(
            1,
            count($properties)
        );

        $this->assertEquals(
            $this->test_property_name,
            $properties[0]['name']
        );

        $this->assertEquals(
            'text',
            $properties[0]['type']
        );

        $this->assertEquals(
            'test',
            $properties[0]['state']
        );

        $this->assertTrue(
            $properties[0]['requestable']
        );
    }

    public function testResourceAddChild()
    {
        //$this->resource has been created in the _before() method.

        $this->child = $this->resource_cat->createResource(
            'Test Resource Child 20171013',
            'Resource Child Description 20171013'
        );

        $this->resource->addChild($this->child);

        $this->assertEquals(
            $this->resource->children[0]->id,
            $this->child->id
        );

        $this->assertEquals(
            $this->child->parent_id,
            $this->resource->id
        );

        $this->assertEquals(
            $this->child->level,
            ($this->resource->level+1)
        );
    }

    public function testResourceIsAssigned()
    {
        //$this->resource has been created
        //in the _before()-Method.

        $this->assertTrue(
            $this->resource->isAssigned(
                $this->booking_start_time,
                $this->booking_end_time
            )
        );
    }

    public function testResourceIsUnassigned()
    {
        $this->assertFalse(
            $this->resource->isAssigned(
                new DateTime('2016-10-02 7:30:00 +0000'),
                new DateTime('2016-10-02 7:59:59 +0000')
            )
        );
    }

    public function testResourceIsAssignedWithInvalidTimeRange()
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->resource->isAssigned(
            new DateTime('2017-10-02 23:59:59 +0000'),
            new DateTime('2017-10-02 0:00:00 +0000')
        );
    }

    public function testGetResourceBookings()
    {
        //$this->resource has been created in the _before() method.

        $bookings = $this->resource->getResourceBookings(
            new DateTime('2017-01-01 0:00:00 +0000'),
            new DateTime('2017-12-31 23:59:59 +0000')
        );

        $this->assertEquals(
            count($bookings),
            1
        );

        $this->assertEquals(
            $bookings[0]->id,
            $this->booking->id
        );

        $this->assertEquals(
            $bookings[0]->begin,
            $this->booking->begin
        );

        $this->assertEquals(
            $bookings[0]->end,
            $this->booking->end
        );
    }

    public function testResourceIsLocked()
    {
        $this->assertTrue(
            $this->resource->isLocked(
                new DateTime('2017-11-01 8:00:00 +0000'),
                new DateTime('2017-11-02 18:00:00 +0000')
            )
        );
    }

    public function testResourceIsNotLocked()
    {
        $this->assertFalse(
            $this->resource->isLocked(
                new DateTime('2017-11-20 8:00:00 +0000'),
                new DateTime('2017-11-20 18:00:00 +0000')
            )
        );
    }

    public function testResourceIsLockedWithInvalidTimeRange()
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->resource->isLocked(
            new DateTime('2017-11-02 23:59:59 +0000'),
            new DateTime('2017-11-01 0:00:00 +0000')
        );
    }

    //Resource::isAvailable does not need to be tested since
    //failure of one of the tests for isAvailable and isLocked would
    //imply that a test for isAvailable would also fail.

    public function testGetFullName()
    {
        $this->assertEquals(
            'Ressource ' . $this->test_resource_name,
            $this->resource->getFullName()
        );
    }

    //testUserPermissions tests all user permission methods.
    public function testUserPermissions()
    {
        //$this->test_user should have admin permissions
        //on $this->resource since $this->test_user is the owner
        //of $this->resource.

        $this->assertEquals(
            'admin',
            $this->resource->getUserPermission(
                $this->test_user
            )
        );

        //user2 is set up in the _before() method and user2's
        //permission level on $this->resource is set to admin.

        $user2_perms = $this->resource->getUserPermission($this->user2);

        $this->assertEquals(
            'admin',
            $user2_perms
        );

        //user2 should have all lower privileges since user2
        //has admin permission on $this->resource:
        $this->assertTrue(
            $this->resource->userHasPermission(
                $this->user2,
                'user'
            )
        );
        $this->assertTrue(
            $this->resource->userHasPermission(
                $this->user2,
                'autor'
            )
        );
        $this->assertTrue(
            $this->resource->userHasPermission(
                $this->user2,
                'tutor'
            )
        );
        $this->assertTrue(
            $this->resource->userHasPermission(
                $this->user2,
                'admin'
            )
        );

        //now we delete user2's permission and give user2
        //another permission level:

        $this->assertTrue(
            $this->resource->deleteUserPermission($this->user2)
        );

        $this->assertTrue(
            $this->resource->setUserPermission(
                $this->user2,
                'tutor'
            )
        );

        $this->assertEquals(
            'tutor',
            $this->resource->getUserPermission($this->user2)
        );

        //Ok, we now delete every permission for
        //$this->resource:
        $this->assertTrue(
            $this->resource->deleteAllPermissions()
        );

        //Now user2 should have no permissions on $this->resource:
        $this->assertEquals(
            '',
            $this->resource->getUserPermission($this->user2)
        );
    }

    public function testGetURL()
    {
        //By testing the getURL method we have automatically tested
        //the methods getLink, getURLForAction and getLinkForAction
        //since all those methods generate the same URL. The "link"-methods
        //just generate a HTML compliant representation of them.
        $link = $this->resource->getActionURL('show');

        $this->assertEquals(
            'dispatch.php/resources/resource/index/' . $this->resource->id,
            $link
        );

        $link = $this->resource->getActionURL('show', ['test' => '1']);

        $this->assertEquals(
            'dispatch.php/resources/resource/index/' . $this->resource->id . '?test=1',
            $link
        );

        $link = $this->resource->getActionURL('delete');

        $this->assertEquals(
            'dispatch.php/resources/resource/delete/' . $this->resource->id,
            $link
        );
    }
}
