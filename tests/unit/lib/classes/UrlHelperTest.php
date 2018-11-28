<?php
/*
 * url_helper_test.php - unit tests for the URLHelper class
 *
 * Copyright (c) 2008  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once 'lib/classes/URLHelper.php';

class URLHelperTest extends PHPUnit_Framework_TestCase
{
    public function tearDown ()
    {
        // remove all link params
        foreach (URLHelper::getLinkParams() as $param => $value) {
            URLHelper::removeLinkParam($param);
        }

        URLHelper::setBaseURL(NULL);
    }

    public function testNoLinkParam ()
    {
        $this->assertEquals('?', URLHelper::getURL(''));
        $this->assertEquals('x', URLHelper::getURL('x'));
        $this->assertEquals('?#x', URLHelper::getURL('#x'));

        URLHelper::setBaseURL('/dir/');

        $this->assertEquals('?', URLHelper::getURL(''));
        $this->assertEquals('?#x', URLHelper::getURL('#x'));
        $this->assertEquals('?a=b', URLHelper::getURL('?a=b'));
    }

    public function testAddLinkParam ()
    {
        $this->assertEquals(array(), URLHelper::getLinkParams());

        URLHelper::addLinkParam('foo', 'bar');
        $this->assertEquals(array('foo' => 'bar'), URLHelper::getLinkParams());

        URLHelper::addLinkParam('answer', 42);
        $this->assertEquals(array('foo' => 'bar', 'answer' => '42'),
                            URLHelper::getLinkParams());
    }

    public function testBindLinkParam ()
    {
        $_REQUEST['var'] = 'old';

        URLHelper::bindLinkParam('var', $var);
        $this->assertEquals('old', $var);

        $var = 'new';
        $this->assertEquals('?var=new', URLHelper::getURL(''));
    }

    public function testRemoveLinkParam ()
    {
        URLHelper::addLinkParam('foo', 'bar');
        URLHelper::addLinkParam('answer', 42);
        URLHelper::removeLinkParam('foo');

        $this->assertEquals('?answer=42', URLHelper::getURL(''));
    }

    public function testGetURL ()
    {
        URLHelper::addLinkParam('null', NULL);
        URLHelper::addLinkParam('empty', array());
        URLHelper::addLinkParam('foo', 'bar');

        $url = 'abc?a=b&c=d#top';
        $expected = 'abc?foo=bar&a=b&c=d#top';
        $this->assertEquals($expected, URLHelper::getURL($url));

        $url = 'abc#top';
        $params = array('a' => 'b', 'c' => 'd');
        $expected = 'abc?foo=bar&a=b&c=d#top';
        $this->assertEquals($expected, URLHelper::getURL($url, $params));

        $url = 'abc?foo=test';
        $expected = 'abc?foo=test';
        $this->assertEquals($expected, URLHelper::getURL($url));

        $url = 'abc';
        $params = array('foo' => 'test');
        $expected = 'abc?foo=test';
        $this->assertEquals($expected, URLHelper::getURL($url, $params));

        $url = 'abc?baz=on';
        $params = array('baz' => 'off');
        $expected = 'abc?foo=bar&baz=off';
        $this->assertEquals($expected, URLHelper::getURL($url, $params));

        $url = 'abc?foo=baz';
        $params = array('foo' => 'test');
        $expected = 'abc?foo=test';
        $this->assertEquals($expected, URLHelper::getURL($url, $params));
    }

    public function testGetLink ()
    {
        URLHelper::addLinkParam('foo', '& ;');
        URLHelper::addLinkParam('bar', '"\'');

        $url = 'abc?a=%26&c="d#1';
        $expected = 'abc?foo=%26+%3B&amp;bar=%22%27&amp;a=%26&amp;c=%22d#1';
        $this->assertEquals($expected, URLHelper::getLink($url));
    }

    public function testSetBaseURL ()
    {
        $this->assertEquals('foo/bar', URLHelper::getLink('foo/bar'));
        $this->assertEquals('/foo/bar', URLHelper::getLink('/foo/bar'));
        $this->assertEquals('http://www.studip.de/foo/bar',
                            URLHelper::getLink('http://www.studip.de/foo/bar'));

        URLHelper::setBaseURL('/dir/');

        $this->assertEquals('/dir/foo/bar', URLHelper::getLink('foo/bar'));
        $this->assertEquals('/foo/bar', URLHelper::getLink('/foo/bar'));
        $this->assertEquals('http://www.studip.de/foo/bar',
                            URLHelper::getLink('http://www.studip.de/foo/bar'));

        URLHelper::setBaseURL('http://cnn.com/test/');

        $this->assertEquals('http://cnn.com/test/foo/bar', URLHelper::getLink('foo/bar'));
        $this->assertEquals('http://cnn.com/foo/bar', URLHelper::getLink('/foo/bar'));
        $this->assertEquals('http://www.studip.de/foo/bar',
                            URLHelper::getLink('http://www.studip.de/foo/bar'));
    }
}
