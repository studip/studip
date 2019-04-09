<?php
/*
 * SimpleCollectionTest - unit tests for the SimpleCollection class
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
require_once 'lib/classes/StudipArrayObject.class.php';
require_once 'lib/models/SimpleCollection.class.php';

class SimpleCollectionTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
    }

    function tearDown()
    {
    }

    public function testConstruct()
    {
        $data[] = ['id' => 1, 'vorname' => 'Ândré', 'nachname' => 'Noack', 'perm' => 'dozent'];
        $data[] = ['id' => 2, 'vorname' => 'Stefan', 'nachname' => 'Suchi', 'perm' => 'dozent'];
        $data[] = ['id' => 10, 'vorname' => 'Élmar', 'nachname' => 'Ludwig', 'perm' => 'admin'];
        $data[] = ['id' => 11, 'vorname' => 'Jan-Hendrik', 'nachname' => 'Wilms', 'perm' => 'tutor'];
        $data[] = ['id' => 15, 'vorname' => 'Nico', 'nachname' => 'Müller', 'perm' => 'root'];

        $a = new SimpleCollection();
        $this->assertInstanceOf('SimpleCollection', $a);
        $a = SimpleCollection::createFromArray($data);
        $this->assertInstanceOf('SimpleCollection', $a);
        $this->assertInstanceOf('ArrayAccess', $a[0]);
        $this->assertEquals($data[0]['id'], $a[0]['id']);
        $this->assertEquals($a->toArray(), $data);
        $finder = function () use ($data) {return $data;};
        $a = new SimpleCollection($finder);
        $this->assertInstanceOf('ArrayAccess', $a[0]);
        $this->assertEquals($data[0]['id'], $a[0]['id']);
        $this->assertEquals($a->toArray(), $data);
        return $a;
    }

    /**
     * @depends testConstruct
     */
    public function testArrayAccess($a)
    {
        $newval = ['id' => 17, 'vorname' => 'Till', 'nachname' => 'Glöggler', 'perm' => 'root'];
        $a[] = $newval;
        $last = count($a) - 1;
        $this->assertEquals(17, $a[$last]->id);
        $a[$last]->id = 18;
        $this->assertEquals(18, $a[$last]['id']);

        $a[] = new ArrayObject($newval);
        $last = count($a) - 1;
        $this->assertEquals(17, $a[$last]->id);
        $a[$last]->id = 18;
        $this->assertEquals(18, $a[$last]['id']);

        $newobj = new stdClass();
        foreach ($newval as $k => $v) $newobj->$k = $v;
        $a[] = $newobj;
        $last = count($a) - 1;
        $this->assertEquals(17, $a[$last]->id);
        $a[$last]->id = 18;
        $this->assertEquals(18, $a[$last]['id']);

        $lastval = array_pop($a->toArray());
        $lastval['id'] = 17;
        $this->assertEquals($newval, $lastval);

        $a->refresh();
    }

    /**
     * @depends testConstruct
     */
    public function testFindBy($a)
    {
        $test = $a->findBy('id', 1);
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(1, $test);
        $test = $a->findBy('id', [1,2]);
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(2, $test);
        $test = $a->findBy('id', '1', '==');
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(1, $test);
        $test = $a->findBy('id', '1', '===');
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(0, $test);
        $test = $a->findBy('id', '1', '!=');
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(4, $test);
        $test = $a->findBy('id', '1', '!==');
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(5, $test);
        $test = $a->findBy('id', 5, '>');
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(3, $test);
        $test = $a->findBy('id', 5, '>=');
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(3, $test);
        $test = $a->findBy('id', [10,15], '><');
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(1, $test);
        $test = $a->findBy('id', [10,15], '>=<=');
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(3, $test);
        $test = $a->findBy('vorname', 'andre', '%=');
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(1, $test);
        $test = $a->findBy('nachname', 'll', '*=');
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(1, $test);
        $test = $a->findBy('nachname', 'Müll', '^=');
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(1, $test);
        $test = $a->findBy('nachname', 'lms', '$=');
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(1, $test);
        $test = $a->findBy('nachname', '/[^a-zA-Z]/', '~=');
        $this->assertInstanceOf('SimpleCollection', $test);
        $this->assertCount(1, $test);
        $one = $a->findOneBy('id', 10);
        $this->assertEquals('Ludwig', $one['nachname']);
        $this->assertEquals('Ludwig', $a->findBy('id', 10)->val('nachname'));
    }

    /**
     * @depends testConstruct
     */
    public function testUnsetBy($a)
    {
        $ok = $a->unsetBy('id', 10);
        $this->assertEquals(1, $ok);
        $this->assertCount(1, $a->getDeleted());
        $this->assertEquals('Ludwig', $a->getDeleted()->val('nachname'));
        $this->assertEquals(5, $a->refresh());
    }

    /**
     * @depends testConstruct
     */
    public function testPluck($a)
    {
        $expected = [1, 2, 10, 11, 15];
        $this->assertEquals($expected, $a->pluck('id'));
        $expected = [[1, 'dozent'], [2, 'dozent'], [10, 'admin'],[11, 'tutor'], [15, 'root']];
        $this->assertEquals($expected, $a->pluck(['id', 'perm']));

    }

    /**
     * @depends testConstruct
     */
    public function testToGroupedArray($a)
    {
        $expected = [];
        $expected[1] = ['nachname' => 'Noack'];
        $expected[2] = ['nachname' => 'Suchi'];
        $expected[10] = ['nachname' => 'Ludwig'];
        $expected[11] = ['nachname' => 'Wilms'];
        $expected[15] = ['nachname' => 'Müller'];
        $this->assertEquals($expected, $a->toGroupedArray('id', ['nachname']));
        $expected = [];
        $expected['dozent'] = 2;
        $expected['admin'] = 1;
        $expected['tutor'] = 1;
        $expected['root'] = 1;
        $group_func = function ($a) {return count($a);};
        $this->assertEquals($expected, $a->toGroupedArray('perm', 'perm', $group_func));
    }

    /**
     * @depends testConstruct
     * @depends testPluck
     */
    public function testLimit($a)
    {
        $expected = [1, 2];
        $this->assertEquals($expected, $a->limit(2)->pluck('id'));
        $expected = [3 => 11, 4 => 15];
        $this->assertEquals($expected, $a->limit(3,2)->pluck('id'));
        $this->assertEquals($expected, $a->limit(-2)->pluck('id'));
        $expected = [2 => 10];
        $this->assertEquals($expected, $a->limit(2,-2)->pluck('id'));
    }

    /**
     * @depends testConstruct
     * @depends testPluck
     */
    public function testOrderBy($a)
    {
        $expected = [ 'Wilms',
                            'Suchi',
                            'Noack',
                            'Müller',
                            'Ludwig'
        ];
        $this->assertEquals($expected, array_values($a->orderBy('nachname desc')->pluck('nachname')));
        $this->assertEquals(array_reverse($expected), array_values($a->orderBy('nachname asc')->pluck('nachname')));
        $expected =  [
                        'Jan-Hendrik',
                        'Nico',
                        'Stefan',
                        'Ândré',
                        'Élmar'
        ];
        $this->assertEquals($expected, array_values($a->orderBy('vorname asc', SORT_STRING)->pluck('vorname')));
        $expected =  [
                         'Ândré',
                         'Élmar',
                         'Jan-Hendrik',
                         'Nico',
                         'Stefan'
        ];
        $this->assertEquals($expected, array_values($a->orderBy('vorname asc', SORT_LOCALE_STRING)->pluck('vorname')));
        $expected = [1,2,10,11,15];
        $this->assertEquals($expected, array_values($a->orderBy('id asc', SORT_NUMERIC)->pluck('id')));
    }

    /**
     * @depends testConstruct
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage unknown operator: foo
     */
    public function testInvalidCompOperatorException($a)
    {
        SimpleCollection::getCompFunc('foo', null);
    }

    /**
     * @depends testConstruct
     * @depends testOrderBy
     */
    public function testMerge($a)
    {
        $data[] = ['id' => 19, 'vorname' => 'Marcus', 'nachname' => 'Eibrink-Lunzenauer', 'perm' => 'dozent'];
        $data[] = ['id' => 20, 'vorname' => 'Rasmus', 'nachname' => 'Fuhse', 'perm' => 'root'];

        $a->merge(new SimpleCollection($data));
        $this->assertCount(7, $a);
        $expected = [1,2,10,11,15,19,20];
        $this->assertEquals($expected, array_values($a->orderBy('id asc', SORT_NUMERIC)->pluck('id')));
    }
}
