<?php
/*
 * notification_center_test.php - unit tests for the NotificationCenter class
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once 'lib/classes/NotificationCenter.class.php';

interface Observer
{
    public function update($event, $object, $user_data);
}

# sample predicate (implemented as a Callable class)
# using #soundex for comparisons
# @see http://php.net/language.oop5.magic
class NotificationCenterTestSoundexPredicate
{
    public function __construct($goldStandard)
    {
        $this->goldStandard = $goldStandard;
    }

    public function __invoke($arg)
    {
        return soundex($this->goldStandard) === soundex($arg);
    }
}

class NotificationCenterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->observer = $this->getMock("Observer");
        $this->subject = new stdClass();

        NotificationCenter::addObserver($this->observer, 'update', NULL);
        NotificationCenter::addObserver($this->observer, 'update', NULL, $this->subject);
        NotificationCenter::addObserver($this->observer, 'update', 'foo');
        NotificationCenter::addObserver($this->observer, 'update', 'foo', $this->subject);
    }

    public function tearDown()
    {
        NotificationCenter::removeObserver($this->observer);
    }

    public function testAddObserver1()
    {
        $this->observer->expects($this->exactly(4))->method('update');

        NotificationCenter::postNotification('foo', $this->subject);
    }

    public function testAddObserver2()
    {
        $this->observer->expects($this->exactly(2))->method('update');

        NotificationCenter::postNotification('bar', $this->subject);
    }

    public function testAddObserver3()
    {
        $this->observer->expects($this->exactly(2))->method('update');

        NotificationCenter::postNotification('foo', 'other');
    }

    public function testAddObserver4()
    {
        $this->observer->expects($this->once())->method('update');

        NotificationCenter::postNotification('bar', 'other');
    }

    public function testPostNotification()
    {

        $user_data = [42];
        $this->observer->expects($this->exactly(4))
            ->method('update')
            ->with("foo", $this->subject, $user_data);

        NotificationCenter::postNotification('foo', $this->subject, $user_data);
    }

    public function testRemoveOtherObserver()
    {
        $this->observer->expects($this->exactly(4))->method('update');

        $observer = $this->getMock("Observer");
        NotificationCenter::removeObserver($observer);
        NotificationCenter::postNotification('foo', $this->subject);
    }

    public function testRemoveObserver1()
    {
        $this->observer->expects($this->never())->method('update');

        NotificationCenter::removeObserver($this->observer);
        NotificationCenter::postNotification('foo', $this->subject);
        NotificationCenter::postNotification('bar', $this->subject);
        NotificationCenter::postNotification('foo', 'other');
        NotificationCenter::postNotification('bar', 'other');
    }

    public function testRemoveObserver2a()
    {
        $this->observer->expects($this->exactly(2))->method('update');

        NotificationCenter::removeObserver($this->observer, 'foo');
        NotificationCenter::postNotification('foo', $this->subject);
    }

    public function testRemoveObserver2b()
    {
        $this->observer->expects($this->exactly(2))->method('update');

        NotificationCenter::removeObserver($this->observer, 'foo');
        NotificationCenter::postNotification('bar', $this->subject);
    }

    public function testRemoveObserver2c()
    {
        $this->observer->expects($this->once())->method('update');

        NotificationCenter::removeObserver($this->observer, 'foo');
        NotificationCenter::postNotification('foo', 'other');
    }

    public function testRemoveObserver2d()
    {
        $this->observer->expects($this->once())->method('update');

        NotificationCenter::removeObserver($this->observer, 'foo');
        NotificationCenter::postNotification('bar', 'other');
    }

    public function testRemoveObserver3a()
    {
        $this->observer->expects($this->exactly(2))->method('update');

        NotificationCenter::removeObserver($this->observer, NULL, $this->subject);
        NotificationCenter::postNotification('foo', $this->subject);
    }

    public function testRemoveObserver3b()
    {
        $this->observer->expects($this->once())->method('update');

        NotificationCenter::removeObserver($this->observer, NULL, $this->subject);
        NotificationCenter::postNotification('bar', $this->subject);
    }

    public function testRemoveObserver3c()
    {
        $this->observer->expects($this->exactly(2))->method('update');

        NotificationCenter::removeObserver($this->observer, NULL, $this->subject);
        NotificationCenter::postNotification('foo', 'other');
    }

    public function testRemoveObserver3d()
    {
        $this->observer->expects($this->once())->method('update');

        NotificationCenter::removeObserver($this->observer, NULL, $this->subject);
        NotificationCenter::postNotification('bar', 'other');
    }

    public function testRemoveObserver4a()
    {
        $this->observer->expects($this->exactly(3))->method('update');

        NotificationCenter::removeObserver($this->observer, 'foo', $this->subject);
        NotificationCenter::postNotification('foo', $this->subject);
    }

    public function testRemoveObserver4b()
    {
        $this->observer->expects($this->exactly(2))->method('update');

        NotificationCenter::removeObserver($this->observer, 'foo', $this->subject);
        NotificationCenter::postNotification('bar', $this->subject);
    }

    public function testRemoveObserver4c()
    {
        $this->observer->expects($this->exactly(2))->method('update');

        NotificationCenter::removeObserver($this->observer, 'foo', $this->subject);
        NotificationCenter::postNotification('foo', 'other');
    }

    public function testRemoveObserver4d()
    {
        $this->observer->expects($this->once())->method('update');

        NotificationCenter::removeObserver($this->observer, 'foo', $this->subject);
        NotificationCenter::postNotification('bar', 'other');
    }


    public function testWildCardObserver()
    {
        // prepare fixtures
        $user_data = [42];
        $subject = new stdClass();

        // register observer
        $wildcard = $this->getMock("Observer");
        $wildcard->expects($this->once())->method('update')->with('foo', $subject, $user_data);

        NotificationCenter::addObserver($wildcard, 'update', NULL);


        // expect notication
        NotificationCenter::postNotification('foo', $subject, $user_data);

        // remove observer
        NotificationCenter::removeObserver($wildcard);
    }

    public function testLikeObserver()
    {
        // prepare fixtures
        $user_data = [42];
        $subject = new stdClass();

        // register observer
        $wildcard = $this->getMock("Observer");
        $wildcard->expects($this->once())->method('update')->with('foobar', $subject, $user_data);

        NotificationCenter::addObserver($wildcard, 'update', 'foo*');


        // expect notication
        NotificationCenter::postNotification('foobar', $subject, $user_data);

        NotificationCenter::postNotification('tafkap', $subject, $user_data);

        // remove observer
        NotificationCenter::removeObserver($wildcard);
    }

    function assertMatchingNotification($matcher, $subject)
    {
        // register observer
        $observer = $this->getMock("Observer");
        NotificationCenter::addObserver($observer, 'update',
                                        'SomeNotification', $matcher);

        // expect notication
        $observer->expects($this->once())->method('update')->with('SomeNotification', $subject);

        // fire!
        NotificationCenter::postNotification('SomeNotification', $subject);

        // remove observer
        NotificationCenter::removeObserver($observer);
    }

    public function testCustomPredicateWithAnonFunc()
    {
        $matcher = function ($subject) {
                return preg_match('@^/road/to@', $subject);
        };
        $subject = "/road/to/nowhere";

        $this->assertMatchingNotification($matcher, $subject);
    }

    public function testCustomPredicateWithCallable()
    {
        $matcher = new NotificationCenterTestSoundexPredicate("leopard");
        $subject = "lab hurt";

        $this->assertMatchingNotification($matcher, $subject);
    }

}
