<?php

/*
 * Copyright (C) 2009 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once 'lib/phplib/Seminar_Perm.class.php';
require_once 'lib/classes/Avatar.class.php';
require_once 'lib/classes/CourseAvatar.class.php';
require_once 'lib/classes/InstituteAvatar.class.php';
require_once 'lib/models/SimpleORMap.class.php';
require_once 'lib/classes/Visibility.php';

/**
 * Testcase for Avatar class.
 *
 * @package    studip
 * @subpackage test
 *
 * @author    mlunzena
 * @copyright (c) Authors
 */
class AvatarTestCase extends PHPUnit_Framework_TestCase {

    function setUp()
    {
        $stub = $this->getMock('Seminar_Perm');
        // Configure the stub.
        $stub->expects($this->any())
            ->method('have_perm')
            ->will($this->returnValue(true));

        $GLOBALS['perm'] = $stub;
        $GLOBALS['DYNAMIC_CONTENT_URL'] = "/dynamic";
        $GLOBALS['DYNAMIC_CONTENT_PATH'] = "/dynamic";
        $this->avatar_id = "123456789";
        $this->avatar = Avatar::getAvatar($this->avatar_id);
    }

  function tearDown() {
    unset($GLOBALS['DYNAMIC_CONTENT_PATH'], $GLOBALS['DYNAMIC_CONTENT_URL']);
  }

  function test_class_should_exist() {
    $this->assertTrue(class_exists('Avatar'));
  }

  function test_avatar_url() {
    $url = $this->avatar->getCustomAvatarUrl(Avatar::NORMAL);
    $this->assertEquals("/dynamic/user/" . $this->avatar_id . "_normal.png?d=0", $url);
  }

  function test_avatar_path() {
    $path = $this->avatar->getCustomAvatarPath(Avatar::NORMAL);
    $this->assertEquals("/dynamic/user/" . $this->avatar_id . "_normal.png", $path);
  }

  function test_nobody_url() {
    $url = Avatar::getNobody()->getUrl(Avatar::NORMAL);
    $this->assertEquals("/dynamic/user/nobody_normal.png?d=0", $url);
  }

  function test_nobody_path() {
    $path = Avatar::getNobody()->getCustomAvatarPath(Avatar::NORMAL);
    $this->assertEquals("/dynamic/user/nobody_normal.png", $path);
  }
}


class CourseAvatarTestCase extends PHPUnit_Framework_TestCase
{

  function setUp() {
    $this->avatar_id = "123456789";
    $this->avatar = CourseAvatar::getAvatar($this->avatar_id);

    $this->setUpFS();

    $GLOBALS['DYNAMIC_CONTENT_URL'] = "/dynamic";
    $GLOBALS['DYNAMIC_CONTENT_PATH'] = "/dynamic";
  }

  function setUpFS() {
    ArrayFileStream::set_filesystem(array(
      'dynamic' => array(
        'course' => array(
          $this->avatar_id . '_normal.png' => '',
          $this->avatar_id . '_medium.png' => '',
          $this->avatar_id . '_small.png' => '',
        ),
      ),
    ));

    if (!stream_wrapper_register("var", "ArrayFileStream")) {
      new Exception("Failed to register protocol");
    }
  }

  function tearDown() {
    stream_wrapper_unregister("var");
    unset($GLOBALS['DYNAMIC_CONTENT_PATH'], $GLOBALS['DYNAMIC_CONTENT_URL']);
  }

  function test_class_should_exist() {
    $this->assertTrue(class_exists('CourseAvatar'));
  }

  function test_avatar_url() {
    $url = $this->avatar->getCustomAvatarUrl(Avatar::NORMAL);
    $this->assertEquals("/dynamic/course/". $this->avatar_id . "_normal.png?d=0", $url);
  }

  function test_avatar_path() {
    $path = $this->avatar->getCustomAvatarPath(Avatar::NORMAL);
    $this->assertEquals("/dynamic/course/". $this->avatar_id . "_normal.png", $path);
  }

  function test_nobody_url() {
    $url = CourseAvatar::getNobody()->getUrl(Avatar::NORMAL);
    $this->assertEquals("/dynamic/course/nobody_normal.png?d=0", $url);
  }

  function test_nobody_path() {
    $path = CourseAvatar::getNobody()->getCustomAvatarPath(Avatar::NORMAL);
    $this->assertEquals("/dynamic/course/nobody_normal.png", $path);
  }
}
