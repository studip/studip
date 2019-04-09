<?php
/*
 * request_test.php - unit tests for the Request class
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once 'lib/classes/Request.class.php';

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function setUp ()
    {
        $_GET['a']    = 'test';
        $_POST['b']   = '\\h1"';
        $_GET['c']    = '-23';
        $_POST['d']   = '12.7';
        $_GET['e']    = '3,14';
        $_POST['s_x'] = '0';
        $_GET['f']    = 'root@studip';

        $_GET['v1']  = ['1', '2.4', '3,7'];
        $_POST['v2'] = ['on\'e', 'two', 'thr33'];
        $_GET['v3']  = ['root@studip', 'hotte.testfreund', 42, '!"$%&/()'];

        if (get_magic_quotes_gpc()) {
            $_GET  = Request::addslashes($_GET);
            $_POST = Request::addslashes($_POST);
        }

        $testconfig = new Config([
            'USERNAME_REGULAR_EXPRESSION' => '/^([a-zA-Z0-9_@.-]{4,})$/',
        ]);
        Config::set($testconfig);
    }

    public function testURL ()
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_NAME'] = 'www.example.com';
        $_SERVER['SERVER_PORT'] = '443';
        $_SERVER['REQUEST_URI'] = '/do/it?now=1';

        $this->assertEquals('https://www.example.com/do/it?now=1', Request::url());

        $_SERVER['HTTPS'] = '';
        $_SERVER['SERVER_NAME'] = 'www.example.com';
        $_SERVER['SERVER_PORT'] = '8080';
        $_SERVER['REQUEST_URI'] = '/index.php';

        $this->assertEquals('http://www.example.com:8080/index.php', Request::url());
    }

    public function testArrayAccess ()
    {
        $request = Request::getInstance();

        $this->assertNull($request['null']);
        $this->assertSame($request['a'], 'test');
        $this->assertSame($request['b'], '\\h1"');
        $this->assertSame($request['c'], '-23');
    }

    public function testSetParam ()
    {
        Request::set('yyy', 'xyzzy');
        Request::set('zzz', [1, 2]);

        $this->assertSame(Request::get('yyy'), 'xyzzy');
        $this->assertSame(Request::getArray('zzz'), [1, 2]);
    }

    public function testStringParam ()
    {
        $this->assertNull(Request::get('null'));
        $this->assertSame(Request::get('null', 'foo'), 'foo');
        $this->assertSame(Request::get('a'), 'test');
        $this->assertSame(Request::get('b'), '\\h1"');
        $this->assertSame(Request::get('c'), '-23');
        $this->assertSame(Request::get('d'), '12.7');
        $this->assertNull(Request::get('v2'));

        $this->assertNull(Request::quoted('null'));
        $this->assertSame(Request::quoted('null', 'foo'), 'foo');
        $this->assertSame(Request::quoted('b'), '\\\\h1\\"');
        $this->assertNull(Request::quoted('v2'));
    }

    public function testOptionParam ()
    {
        $this->assertNull(Request::option('null'));
        $this->assertSame(Request::option('a'), 'test');
        $this->assertNull(Request::option('b'));
        $this->assertNull(Request::option('v1'));
    }

    public function testIntParam ()
    {
        $this->assertNull(Request::int('null'));
        $this->assertSame(Request::int('a'), 0);
        $this->assertSame(Request::int('c'), -23);
        $this->assertSame(Request::int('d'), 12);
        $this->assertSame(Request::int('e'), 3);
        $this->assertNull(Request::int('v1'));
    }

    public function testFloatParam ()
    {
        $this->assertNull(Request::float('null'));
        $this->assertSame(Request::float('a'), 0.0);
        $this->assertSame(Request::float('c'), -23.0);
        $this->assertSame(Request::float('d'), 12.7);
        $this->assertSame(Request::float('e'), 3.14);
        $this->assertNull(Request::float('v1'));
    }

    public function testUsernameParam ()
    {
        $this->assertNull(Request::username('null'));
        $this->assertSame(Request::username('a'), 'test');
        $this->assertSame(Request::username('f'), 'root@studip');
        $this->assertNull(Request::username('b'));
        $this->assertNull(Request::username('v1'));
    }

    public function testStringArrayParam ()
    {
        $this->assertSame(Request::getArray('null'), []);
        $this->assertSame(Request::getArray('b'), []);
        $this->assertSame(Request::getArray('v1'), ['1', '2.4', '3,7']);
        $this->assertSame(Request::getArray('v2'), ['on\'e', 'two', 'thr33']);

        $this->assertSame(Request::quotedArray('null'), []);
        $this->assertSame(Request::quotedArray('b'), []);
        $this->assertSame(Request::quotedArray('v1'), ['1', '2.4', '3,7']);
        $this->assertSame(Request::quotedArray('v2'), ['on\\\'e', 'two', 'thr33']);
    }

    public function testOptionArrayParam ()
    {
        $this->assertSame(Request::optionArray('null'), []);
        $this->assertSame(Request::optionArray('a'), []);
        $this->assertSame(Request::optionArray('v1'), ['1']);
        $this->assertSame(Request::optionArray('v2'), [1 => 'two', 2 => 'thr33']);
    }

    public function testIntArrayParam ()
    {
        $this->assertSame(Request::intArray('null'), []);
        $this->assertSame(Request::intArray('c'), []);
        $this->assertSame(Request::intArray('v1'), [1, 2, 3]);
        $this->assertSame(Request::intArray('v2'), [0, 0, 0]);
    }

    public function testFloatArrayParam ()
    {
        $this->assertSame(Request::floatArray('null'), []);
        $this->assertSame(Request::floatArray('c'), []);
        $this->assertSame(Request::floatArray('v1'), [1.0, 2.4, 3.7]);
        $this->assertSame(Request::floatArray('v2'), [0.0, 0.0, 0.0]);
    }

    public function testUsernameArrayParam ()
    {
        $this->assertSame(Request::usernameArray('null'), []);
        $this->assertSame(Request::usernameArray('a'), []);
        $this->assertSame(Request::usernameArray('v1'), []);
        $this->assertSame(Request::usernameArray('v2'), [2 => 'thr33']);
        $this->assertSame(Request::usernameArray('v3'), ['root@studip', 'hotte.testfreund']);
    }

    public function testSubmitted ()
    {
        $this->assertFalse(Request::submitted('null'));
        $this->assertTrue(Request::submitted('s'));
        $this->assertTrue(Request::submitted('v1'));
    }

    public function testSubmittedSome ()
    {
        $this->assertFalse(Request::submittedSome('null', 'null'));
        $this->assertTrue(Request::submittedSome('null', 's', 'v'));
    }

    public function tearDown()
    {
        Config::set(null);
    }
}

class RequestMethodTest extends PHPUnit_Framework_TestCase
{
    public function setUp ()
    {
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    protected function setRequestMethod($method)
    {
        $_SERVER['REQUEST_METHOD'] = (string) $method;
    }

    public function testMethod()
    {
        $this->setRequestMethod('GET');
        $this->assertEquals('GET', Request::method());
    }

    public function testMethodUppercases()
    {
        $this->setRequestMethod('gEt');
        $this->assertEquals('GET', Request::method());
    }

    public function testRequestMethodGet()
    {
        $this->setRequestMethod('GET');
        $this->assertTrue(Request::isGet());
    }

    public function testRequestMethodPost()
    {
        $this->setRequestMethod('POST');
        $this->assertTrue(Request::isPost());
    }

    public function testRequestMethodPut()
    {
        $this->setRequestMethod('PUT');
        $this->assertTrue(Request::isPut());
    }

    public function testRequestMethodDelete()
    {
        $this->setRequestMethod('DELETE');
        $this->assertTrue(Request::isDelete());
    }

    public function testIsNotXhr()
    {
        $this->assertFalse(Request::isXhr());
        $this->assertFalse(Request::isAjax());
    }

    public function testIsXhr()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XmlHttpRequest';
        $this->assertTrue(Request::isAjax());
        $this->assertTrue(Request::isXhr());
    }
}
