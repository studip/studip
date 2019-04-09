<?php

/*
 * Copyright (C) 2010 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once dirname(__FILE__) . '/../bootstrap.php';
require_once 'lib/functions.php';

class StringWrapper {
    function __construct($string)
    {
        $this->string = $string;
    }
    function __toString()
    {
        return (string) $this->string;
    }
}

class FunctionsTest extends PHPUnit_Framework_TestCase
{
    function testWords()
    {
        $string = "one two three";
        $this->assertEquals(['one', 'two', 'three'], words($string));
    }

    function testWordsWithEmptyString()
    {
        $string = "";
        $this->assertEquals([], words($string));
    }

    function testArrayFlatten()
    {
        $array = json_decode(
            '[[1, 2], [3, [4], [[5, 6]]], 7]'
        );

        $this->assertEquals(range(1, 7), array_flatten($array));
    }
    
    function testRelsize()
    {
        // Test basic sizes and suffixed 's' if value is <> 1
        $this->assertEquals('0 Bytes', relsize(0));
        $this->assertEquals('0 B', relsize(0, false));
        $this->assertEquals('1 Byte', relsize(1));
        $this->assertEquals('1 B', relsize(1, false));
        $this->assertEquals('2 Bytes', relsize(2));
        $this->assertEquals('2 B', relsize(2, false));

        // Test all sizes
        $this->assertEquals('1 Kilobyte', relsize(pow(1024, 1)));
        $this->assertEquals('1 Megabyte', relsize(pow(1024, 2)));
        $this->assertEquals('1 Gigabyte', relsize(pow(1024, 3)));
        $this->assertEquals('1 Terabyte', relsize(pow(1024, 4)));
        $this->assertEquals('1 Petabyte', relsize(pow(1024, 5)));
        $this->assertEquals('1 Exabyte', relsize(pow(1024, 6)));
        $this->assertEquals('1 Zettabyte', relsize(pow(1024, 7)));
        $this->assertEquals('1 Yottabyte', relsize(pow(1024, 8)));
        
        // Test displayed levels
        $this->assertEquals('1 Megabyte', relsize(1024 * 1024 + 2 * 1024 + 3, true, 1));
        $this->assertEquals('1.5 Megabytes', relsize(1024 * 1024 + 512 * 1024 + 3, true, 1));
        $this->assertEquals('1 Megabyte, 2 Kilobytes', relsize(1024 * 1024 + 2 * 1024 + 3, true, 2));
        $this->assertEquals('1 Megabyte, 2 Kilobytes, 3 Bytes', relsize(1024 * 1024 + 2 * 1024 + 3, true, 3));
        $this->assertEquals('1 Megabyte, 2 Kilobytes, 3 Bytes', relsize(1024 * 1024 + 2 * 1024 + 3, true, 0));
    }
}
