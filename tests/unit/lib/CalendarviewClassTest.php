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
require_once 'lib/calendar/CalendarView.class.php';
require_once 'lib/classes/PageLayout.php';


class CalendarViewCase extends PHPUnit_Framework_TestCase {


    function setUp() {
    }


    function tearDown() {
    }


    function test_class_should_exist() {
        $this->assertTrue(class_exists('CalendarView'));
    }

    function test_constructor() {
        $this->assertInstanceOf("CalendarView", new CalendarView());
    }

    function test_setHeight() {
        $height = 75;
        $cview = new CalendarView();
        $cview->setHeight($height);
        $this->assertEquals($height, $cview->getHeight());
    }

    function test_setRange() {
        $start_hour = 6;
        $end_hour = 12;
        $cview = new CalendarView();
        $cview->setRange($start_hour, $end_hour);
        $result = $cview->getRange();
        $this->assertEquals($start_hour, $result[0]);
        $this->assertEquals($end_hour, $result[1]);
    }

    function test_addColumn() {
        $view = new CalendarView();
        $title1 = "Mittwoch";
        $id1 = 3;
        $view->addColumn($title1, "", $id1);
        $title2 = "Donnerstag";
        $id2 = 4;
        $view->addColumn($title2, "", $id2);
        $columns = $view->getColumns();
        $this->assertInternalType("array", $columns);
        $this->assertInstanceOf("CalendarColumn", $columns[0]);
        $this->assertEquals($title1, $columns[0]->getTitle());
        $this->assertEquals($id1, $columns[0]->getId());
        $this->assertInstanceOf("CalendarColumn", $columns[1]);
        $this->assertEquals($title2, $columns[1]->getTitle());
        $this->assertEquals($id2, $columns[1]->getId());
    }

    public function test_negative_addEntry() {
        $this->setExpectedException('InvalidArgumentException');
        $view = new CalendarView();
        $entry = array(
            'title' => "Test Eintrag",
            'start' => "0800",
            'end' => "0900"
        );
        $view->addEntry($entry);
    }

    public function test_addEntry_getEntries() {
        $view = new CalendarView();
        $id = 3;
        $view->addColumn("Montag", "", $id);
        $entry = array(
            'title' => "Test Eintrag",
            'start' => "0800",
            'end' => "0900"
        );
        $view->addEntry($entry);
        $entries = $view->getEntries();
        $this->assertInternalType("array", $entries);
        $this->assertNotNull($entries['day_'.$id]);
    }

    public function test_insertFunction() {
        $view = new CalendarView();
        $js_function_object = 'function () { alert("Watch out, Gringo!"); }';
        $view->setInsertFunction($js_function_object);
        $this->assertEquals($js_function_object, $view->getInsertFunction());
    }

    //Die anderen Methoden muss Till testen.

}


