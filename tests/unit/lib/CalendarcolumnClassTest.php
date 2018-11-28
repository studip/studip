<?php

/*
 * Copyright (C) 2011 - Rasmus Fuhse <fuhse@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


require_once dirname(__FILE__) . '/../bootstrap.php';
require_once 'lib/classes/DbView.class.php';
require_once 'lib/classes/StudipCache.class.php';
require_once 'lib/classes/StudipNullCache.class.php';
require_once 'lib/classes/TextFormat.php';
require_once 'lib/classes/StudipFormat.php';
require_once 'lib/classes/WikiFormat.php';
require_once 'lib/classes/TreeAbstract.class.php';
require_once 'lib/calendar/CalendarColumn.class.php';
require_once 'lib/functions.php';




class CalendarColumnCase extends PHPUnit_Framework_TestCase {


    function setUp() {
    }


    function tearDown() {
    }


    function test_class_should_exist() {
        $this->assertTrue(class_exists('CalendarColumn'));
    }

    function test_create() {
        $this->assertInstanceOf("CalendarColumn", CalendarColumn::create());
    }

    function test_get_id() {
        $id = "test_id";
        $column = new CalendarColumn($id);
        $this->assertEquals($id, $column->getId());
    }

    function test_set_id() {
        $id = "test_id";
        $column = new CalendarColumn("falsche id");
        $column->setId($id);
        $this->assertEquals($id, $column->getId());
    }

    function test_set_title() {
        $title = "test_title";
        $column = new CalendarColumn();
        $column->setTitle($title);
        $this->assertEquals($title, $column->getTitle());
    }

    function test_set_url() {
        $url = URLHelper::getURL("dispatch.php/profile", array("username" => get_username()));
        $column = CalendarColumn::create()->setURL($url);
        $this->assertEquals($url, $column->getURL());
    }

    function test_add_entry() {
        $entry = array('start' => "0800", 'end' => "1000", 'title' => "test_title");
        $column = CalendarColumn::create()->addEntry($entry);
        $entry = array('start' => "1200", 'end' => "1230", 'title' => "test_title_number_2");
        $column->addEntry($entry);
        $entries = $column->getEntries();
        $this->assertInternalType("array", $entries);
        $this->assertEquals(2, count($entries));
        $this->assertNotEquals($entries[0], $entry);
        $this->assertEquals($entry, $entries[1]);
        $this->assertInternalType("array", $entries[1]);
    }

    function test_wrong_entry() {
        $this->setExpectedException('InvalidArgumentException');
        $entry1 = array('start' => "0800", 'end' => "1000");
        $entry2 = array('start' => "1000", 'title' => "test_title");
        $entry3 = array('end' => "1500", 'title' => "test_title");
        $column = CalendarColumn::create()->addEntry($entry1);
        $column = CalendarColumn::create()->addEntry($entry2);
        $column = CalendarColumn::create()->addEntry($entry3);
    }

    function test_add_entries() {
        $entries = array(
            array('start' => "0800", 'end' => "1000", 'title' => "test_title"),
            array('start' => "1200", 'end' => "1400", 'title' => "test_title")
        );
        $column = CalendarColumn::create()->addEntries($entries);
        $this->assertInternalType('array', $column->getEntries());
    }

    function test_erase_entries() {
        $entry = array('start' => "0800", 'end' => "1000", 'title' => "test_title");
        $column = CalendarColumn::create()->addEntry($entry);
        $column->eraseEntries();
        $entries = $column->getEntries();
        $this->assertInternalType("array", $entries);
        $this->assertEquals(0, count($entries));
    }


    //Die anderen Methoden muss Till testen.

}
