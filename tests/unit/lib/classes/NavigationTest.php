<?php
/*
 * navigation_test.php - unit tests for the Navigation class
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once 'lib/classes/Assets.class.php';
require_once 'lib/classes/Icon.class.php';
require_once 'lib/classes/NotificationCenter.class.php';
require_once 'lib/classes/URLHelper.php';
require_once 'lib/navigation/Navigation.php';

class NavigationTest extends PHPUnit_Framework_TestCase
{
    public function setUp ()
    {
        Navigation::setRootNavigation(new Navigation(''));
    }

    public function testBadgeNumber ()
    {
        $navigation = new Navigation('badge-test');
        $this->assertFalse($navigation->hasBadgeNumber());
        $this->assertEquals(0, $navigation->getBadgeNumber());

        $navigation->setBadgeNumber(23);
        $this->assertTrue($navigation->hasBadgeNumber());
        $this->assertEquals(23, $navigation->getBadgeNumber());
    }

    public function testTitle ()
    {
        $navigation = new Navigation('test');
        $this->assertEquals('test', $navigation->getTitle());

        $navigation->setTitle('frob');
        $this->assertEquals('frob', $navigation->getTitle());
    }

    public function testImage ()
    {
        $navigation = new Navigation('test', 'foo.php');
        $this->assertNull($navigation->getImage());

        $icon = Icon::create('foo', 'clickable');
        $link_attrs = ['alt' => 'foo'];

        $navigation->setImage($icon, $link_attrs);

        $this->assertTrue($navigation->isVisible(true));
        $this->assertEquals($icon, $navigation->getImage());
        $this->assertEquals($link_attrs, $navigation->getLinkAttributes());
    }

    public function testActiveImage ()
    {
        $foo = Icon::create('foo', 'clickable');
        $bar = Icon::create('bar', 'clickable');

        $navigation = new Navigation('test', 'foo.php');
        $navigation->setImage($foo);
        $navigation->setActiveImage($bar);

        $this->assertEquals($foo, $navigation->getImage());
        $navigation->setActive(true);
        $this->assertEquals($bar, $navigation->getImage());
    }

    public function testURL ()
    {
        $navigation = new Navigation('test', 'foo.php');
        $this->assertEquals($navigation->getURL(), 'foo.php');

        $navigation->setURL('bar.php', ['fuzz' => 'yes']);
        $this->assertEquals('bar.php?fuzz=yes', $navigation->getURL());
        $this->assertTrue($navigation->isEnabled());

        $navigation->setEnabled(false);
        $this->assertFalse($navigation->isEnabled());
        $this->assertFalse($navigation->isVisible(true));
        $this->assertTrue($navigation->isVisible());

        $navigation->setURL(NULL);
        $this->assertFalse($navigation->isVisible());
    }

    public function testNavigation ()
    {
        $navigation = new Navigation('test');
        $nav1 = new Navigation('foo', NULL);
        $nav2 = new Navigation('bar', 'bar.php');
        $nav3 = new Navigation('baz', 'baz.php');
        $nav4 = new Navigation('egg', 'egg.php');
        $this->assertNull($navigation->getURL());
        $this->assertEquals([], $navigation->getSubNavigation());

        $navigation->addSubNavigation('a1', $nav1);
        $navigation->addSubNavigation('a2', $nav2);
        $navigation->addSubNavigation('a3', $nav3);
        $nav2->addSubNavigation('b1', $nav4);
        $this->assertFalse($navigation->isActive());
        $this->assertEquals('bar.php', $navigation->getURL());
        $this->assertEquals(['a1' => $nav1, 'a2' => $nav2, 'a3' => $nav3],
                            $navigation->getSubNavigation());

        $nav4->setActive(true);
        $this->assertTrue($navigation->isActive());
        $this->assertSame($navigation->activeSubNavigation(), $nav2);
        $this->assertSame($nav2->activeSubNavigation(), $nav4);

        $navigation->removeSubNavigation('a3');
        $navigation->insertSubNavigation('a3', $nav3, 'a2');
        $navigation->removeSubNavigation('a1');
        $nav2->insertSubNavigation('a1', $nav1, '');
        $this->assertEquals('baz.php', $navigation->getURL());
        $this->assertEquals(['a3' => $nav3, 'a2' => $nav2],
                            $navigation->getSubNavigation());
        $this->assertEquals(['b1' => $nav4, 'a1' => $nav1],
                            $nav2->getSubNavigation());
    }

    public function testNavigationTree ()
    {
        $navigation = new Navigation('test');
        $nav1 = new Navigation('foo', NULL);
        $nav2 = new Navigation('bar', 'bar.php');
        $nav3 = new Navigation('baz', 'baz.php');
        $nav4 = new Navigation('egg', 'egg.php');
        $this->assertFalse(Navigation::hasItem('/test/a2'));

        Navigation::addItem('/test', $navigation);
        Navigation::addItem('/test/a1', $nav1);
        Navigation::addItem('/test/a2', $nav2);
        Navigation::addItem('/test/a3', $nav3);
        Navigation::addItem('/test/a2/b1', $nav4);
        $this->assertTrue(Navigation::hasItem('/test/a2'));
        $this->assertFalse(Navigation::getItem('/test')->isActive());
        $this->assertEquals('bar.php', Navigation::getItem('/test')->getURL());
        $this->assertEquals(['a1' => $nav1, 'a2' => $nav2, 'a3' => $nav3],
                            Navigation::getItem('/test')->getSubNavigation());

        Navigation::activateItem('/test/a2/b1');
        $this->assertTrue(Navigation::getItem('/test')->isActive());
        $this->assertTrue(Navigation::getItem('/test/a2')->isActive());
        $this->assertTrue(Navigation::getItem('/test/a2/b1')->isActive());

        Navigation::removeItem('/test/a3');
        Navigation::insertItem('/test/a3', $nav3, 'a2');
        Navigation::removeItem('/test/a1');
        Navigation::insertItem('/test/a2/a1', $nav1, '');
        $this->assertEquals('baz.php', Navigation::getItem('/test')->getURL());
        $this->assertEquals(['a3' => $nav3, 'a2' => $nav2],
                            Navigation::getItem('/test')->getSubNavigation());
        $this->assertEquals(['b1' => $nav4, 'a1' => $nav1],
                            Navigation::getItem('/test/a2')->getSubNavigation());
    }

    public function testExceptionOnGet ()
    {
        $this->setExpectedException('InvalidArgumentException');
        Navigation::getItem('/test');
    }

    public function testExceptionOnAdd ()
    {
        $navigation = new Navigation('test');
        $this->setExpectedException('InvalidArgumentException');
        Navigation::addItem('/test/foo', $navigation);
    }
}

class NavigationNotificationTest extends PHPUnit_Framework_TestCase
{

    public function testNotificationOnActivation ()
    {
        $navigation = new Navigation('test');
        Navigation::addItem('/test', $navigation);

        $observer = $this->getMock("NotificationObserver");
        $observer->expects($this->once())
            ->method('update')
            ->with($this->equalTo('NavigationDidActivateItem'),
                   $this->equalTo('/test'));

        NotificationCenter::addObserver($observer,
                                        'update',
                                        'NavigationDidActivateItem',
                                        '/test');

        Navigation::activateItem('/test');
    }
}

class NotificationObserver {

    function update($event, $subject, $user_data)
    {
        # will never run
        throw new RuntimeException();
    }
}
