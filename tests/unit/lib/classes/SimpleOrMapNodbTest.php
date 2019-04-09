<?php
/*
 * SimpleOrMapNodbTest - unit tests for the SimpleOrMap class without database access
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once 'lib/models/SimpleORMap.class.php';
require_once 'lib/classes/Config.class.php';
require_once 'lib/classes/StudipCache.class.php';
require_once 'lib/classes/StudipArrayObject.class.php';
require_once 'lib/classes/MultiDimArrayObject.class.php';
require_once 'lib/classes/CSVArrayObject.class.php';
require_once 'lib/classes/JSONArrayObject.class.php';
require_once 'lib/classes/NotificationCenter.class.php';



class auth_user_md5 extends SimpleORMap
{
    public $additional_dummy_data = null;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'auth_user_md5';
        $config['additional_fields']['additional']['get'] = function ($record, $field) {return $record->additional_dummy_data;};
        $config['additional_fields']['additional']['set'] = function ($record, $field, $data) {return $record->additional_dummy_data = $data;};
        $config['serialized_fields']['csvdata'] = 'CSVArrayObject';
        $config['serialized_fields']['jsondata'] = 'JSONArrayObject';
        $config['notification_map']['after_store'] = 'auth_user_md5DidCreateOrUpdate';

        parent::configure($config);
    }

    function getPerms()
    {
        return 'ok:' . $this->content['perms'];
    }

    function setPerms($perm)
    {
        return $this->content['perms'] = mb_strtolower($perm);
    }

    public function registerCallback($types, $cb)
    {
        return parent::registerCallback($types, $cb);
    }
}

class SimpleOrMapNodbTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        StudipTestHelper::set_up_tables(['auth_user_md5']);
    }

    function tearDown()
    {
        StudipTestHelper::tear_down_tables();
    }

    public function testConstruct()
    {
        $a = new auth_user_md5();
        $this->assertInstanceOf('SimpleOrMap', $a);
        return $a;
    }

    /**
     * @depends testConstruct
     */
    public function testMetaData($a)
    {
        $meta = $a->getTableMetadata();
        //$this->assertEquals('auth_user_md5', $meta['db_table']);
        $this->assertEquals('user_id', $meta['pk'][0]);
        $this->assertArrayHasKey('email', $meta['fields']);
    }

    /**
     * @depends testConstruct
     */
    public function testDefaults($a)
    {
        $this->assertEquals(null, $a->email);
        $this->assertEquals('unknown', $a->visible);
        $this->assertEquals('', $a->validation_key);
        $this->assertInstanceOf('CSVArrayObject', $a->csvdata);
        $this->assertEquals('1,3', (string)$a->csvdata);
        $this->assertInstanceOf('JSONArrayObject', $a->jsondata);
    }

    /**
     * @depends testConstruct
     */
    public function testGetterAndSetter($a)
    {
        $mail = 'noack@data-quest';
        $a->email = $mail;
        $this->assertEquals($mail, $a->email);
        $this->assertEquals($mail, $a->EMAIL);
        $mail = 'anoack@data-quest';
        $a['email'] = $mail;
        $this->assertEquals($mail, $a['email']);
        $a->perms = 'ADMIN';
        $this->assertEquals('ok:admin', $a['perms']);
        $a->csvdata = '1,2,3,4,5';
        $this->assertInstanceOf('CSVArrayObject', $a->csvdata);
        $this->assertEquals('1,2,3,4,5', (string)$a->csvdata);
        $this->assertEquals(range(1,5), $a['csvdata']->getArrayCopy());
        $a->jsondata = [0 => 'test1', 1 => 'test2'];
        $this->assertInstanceOf('JSONArrayObject', $a->jsondata);
        $this->assertEquals('["test1","test2"]', (string)$a->jsondata);
        $a->jsondata[] = [1,2,3];
        $this->assertInstanceOf('JSONArrayObject', $a->jsondata[2]);
        $this->assertEquals('["test1","test2",[1,2,3]]', (string)$a->jsondata);
        $a->jsondata[2][] = ['test3' => 'test3'];
        $this->assertEquals('["test1","test2",[1,2,3,{"test3":"test3"}]]', (string)$a->jsondata);
    }

    /**
     * @depends testConstruct
     */
    public function testDirty($a)
    {
        $this->assertEquals(true, $a->isDirty());
        $this->assertEquals(true, $a->isFieldDirty('email'));
        $this->assertEquals(false, $a->isFieldDirty('vorname'));
        $this->assertEquals(true, $a->isFieldDirty('csvdata'));
        $this->assertEquals(true, $a->isFieldDirty('jsondata'));
        $a->csvdata[1] = '3';
        unset($a->csvdata[2]);
        unset($a->csvdata[3]);
        unset($a->csvdata[4]);
        $this->assertEquals(false, $a->isFieldDirty('csvdata'));
    }

    /**
     * @depends testConstruct
     */
    public function testRevert($a)
    {
        $a->revertValue('email');
        $a->revertValue('perms');
        $a->revertValue('csvdata');
        $a->revertValue('jsondata');
        $this->assertEquals(false, $a->isDirty());
        $this->assertEquals(false, $a->isFieldDirty('email'));
    }

    /**
     * @depends testConstruct
     */
    public function testsetData($a)
    {
        $a->vorname = 'André';
        $data['email'] = 'fuhse@data-quest.de';
        $data['vorname'] = 'Rasmus';
        $data['nachname'] = 'Fuhse';
        $data['USERNAME'] = 'krassmus';
        $data['csvdata'] = range(1,4);
        $data['jsondata'] = [0 => [0 => [0 => 1]]];
        $a->setData($data, true);
        $this->assertEquals($data['vorname'], $a->vorname);
        $this->assertEquals($data['nachname'], $a->nachname);
        $this->assertEquals($data['email'], $a->email);
        $this->assertEquals($data['USERNAME'], $a->username);
        $this->assertEquals('1,2,3,4', (string)$a->csvdata);
        $this->assertEquals('[[[1]]]', (string)$a->jsondata);
        $this->assertEquals(false, $a->isDirty());

        $data2['vorname'] = 'Krassmus';
        $data2['username'] = 'rasmus';
        $a->setData($data2, false);
        $this->assertEquals($data2['vorname'], $a->vorname);
        $this->assertEquals($data2['username'], $a->username);
        $this->assertEquals($data['nachname'], $a->nachname);
        $this->assertEquals($data['email'], $a->email);
        $this->assertEquals(true, $a->isDirty());
    }

    /**
     * @depends testConstruct
     */
    public function testPrimaryKey($a)
    {
        $a->setId(1);
        $this->assertEquals(1, $a->user_id);
        $this->assertEquals(1, $a->id);
        $this->assertEquals(1, $a->getId());
        $a->id = 2;
        $this->assertEquals(2, $a->user_id);
        $this->assertEquals(2, $a->id);
        $this->assertEquals(2, $a->getId());
        $a->revertValue('id');
        $this->assertNull($a->id);
        $a->user_id = 2;
    }

    /**
     * @depends testConstruct
     */
    public function testAdditional($a)
    {
        $this->assertNull($a->additional);
        $a->additional = 'test';
        $this->assertEquals($a->additional_dummy_data, $a->additional);
    }

    /**
     * @depends testConstruct
     */
    public function testToArray($a)
    {
        $to_array = $a->toArray();
        $this->assertEquals(2, $to_array['id']);
        $this->assertEquals(2, $to_array['user_id']);
        $this->assertEquals('test', $to_array['additional']);
        $this->assertEquals('ok:user', $to_array['perms']);
        $this->assertEquals(range(1,4), $to_array['csvdata']);
        $this->assertArrayHasKey('visible', $to_array);
        $this->assertCount(17, $to_array);

        $to_array = $a->toArray('id user_id additional perms');
        $this->assertEquals(2, $to_array['id']);
        $this->assertEquals(2, $to_array['user_id']);
        $this->assertEquals('test', $to_array['additional']);
        $this->assertEquals('ok:user', $to_array['perms']);
        $this->assertArrayNotHasKey('visible', $to_array);
        $this->assertCount(4, $to_array);
    }

    /**
     * @depends testConstruct
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage unknown not found.
     */
    public function testInvalidColumnException($a)
    {
        $a->unknown = 1;
    }

    /**
     * @depends testConstruct
     */
    public function testCallback($a)
    {
        $callback_was_here = null;
        $cb = function ($record, $type) use (&$callback_was_here)
        {
            $callback_was_here = $type;
            $record->id = 3;
            return false;
        };
        $a->registerCallback('before_store', $cb);
        $stored = $a->store();
        $this->assertFalse($stored);
        $this->assertEquals(3, $a->id);
        $this->assertEquals('before_store', $callback_was_here);
    }

    /**
     * @depends testConstruct
     */
    public function testNotification($a)
    {
        $callback_was_here = null;
        $cb = function ($type, $record) use (&$callback_was_here)
        {
            $callback_was_here = $type;
            $record->id = 3;
            throw new NotificationVetoException('veto');
        };
        NotificationCenter::addObserver($cb, '__invoke', 'auth_user_md5WillStore', $a);
        $stored = $a->store();
        $this->assertFalse($stored);
        $this->assertEquals(3, $a->id);
        $this->assertEquals('auth_user_md5WillStore', $callback_was_here);
    }
}
