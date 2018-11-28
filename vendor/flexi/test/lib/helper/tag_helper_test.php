<?php

# Copyright (c)  2008 - Marcus Lunzenauer <mlunzena@uos.de>
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.

require_once dirname(__FILE__) . '/../../flexi_tests.php';
Flexi_Tests::setup();
require_once dirname(__FILE__) . '/../../../lib/helper/tag_helper.php';

/**
 * Testcase for TagHelper.
 *
 * @package    flexi
 * @subpackage test
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: tag_helper_test.php 4194 2006-10-24 14:52:31Z mlunzena $
 */

class TagHelperTestCase extends UnitTestCase {

  function setUp() {
  }

  function tearDown() {
  }

  function test_tag() {
    $foo = TagHelper::tag('img', array('src' => 'test.gif'));
    $this->assertEqual('<img src="test.gif" />', $foo);
  }

  function test_content_tag() {
    $foo = TagHelper::content_tag('a', '#', array('href' => '#'));
    $this->assertEqual('<a href="#">#</a>', $foo);
  }

  function test_cdata_section() {
    $foo = TagHelper::cdata_section('foo');
    $this->assertEqual('<![CDATA[foo]]>', $foo);
  }

  function test_string_to_array() {
    $array = TagHelper::string_to_array('id="anId" class=\'aClass\'');
    $this->assertEqual(array('id' => 'anId', 'class' => 'aClass'), $array);
  }
}
