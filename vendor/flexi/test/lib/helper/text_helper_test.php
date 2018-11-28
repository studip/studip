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
require_once dirname(__FILE__) . '/../../../lib/helper/text_helper.php';

/**
 * Testcase for TextHelper.
 *
 * @package    flexi
 * @subpackage test
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */

class TextHelperTestCase extends UnitTestCase {

  function setUp() {
  }

  function tearDown() {
    TextHelper::reset_cycle();
  }

  function test_camelize() {
    $foo = TextHelper::camelize('foo_bar_baz/FOO is great');
    $this->assertEqual('FooBarBazFOOIsGreat', $foo);
  }

  function test_simple_cycle() {
    $this->assertEqual('odd',  TextHelper::cycle('odd', 'even'));
    $this->assertEqual('even', TextHelper::cycle('odd', 'even'));
    $this->assertEqual('odd',  TextHelper::cycle('odd', 'even'));
    $this->assertEqual('even', TextHelper::cycle('odd', 'even'));
  }

  function test_reset_cycle() {
    $this->assertEqual('odd',  TextHelper::cycle('odd', 'even'));
    $this->assertEqual('even', TextHelper::cycle('odd', 'even'));
    $this->assertEqual('odd',  TextHelper::cycle('odd', 'even'));
    TextHelper::reset_cycle();
    $this->assertEqual('odd',  TextHelper::cycle('odd', 'even'));
  }

  function test_double_cycle_should_reset_each_time() {
    $this->assertEqual('odd', TextHelper::cycle('odd', 'even'));
    $this->assertEqual('red', TextHelper::cycle('red', 'black'));
    $this->assertEqual('odd', TextHelper::cycle('odd', 'even'));
    $this->assertEqual('red', TextHelper::cycle('red', 'black'));
  }

  function test_named_cycles() {
    $this->assertEqual('odd',   TextHelper::cycle(array('odd', 'even', 'name' => '1st')));
    $this->assertEqual('red',   TextHelper::cycle('red', 'black'));
    $this->assertEqual('even',  TextHelper::cycle(array('odd', 'even', 'name' => '1st')));
    $this->assertEqual('black', TextHelper::cycle('red', 'black'));
  }
}
