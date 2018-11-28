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

require_once dirname(__FILE__) . '/../flexi_tests.php';
Flexi_Tests::setup();

Mock::generate('Flexi_TemplateFactory');
Mock::generatePartial('Flexi_Template', 'MockTemplate', array('_render'));

class AnEmptyTemplate extends UnitTestCase {


  var $factory;


  function setUp() {
    $this->factory = new MockFlexi_TemplateFactory(TEST_DIR . '/templates/template_tests');
    $template = new MockTemplate();
    $template->__construct('foo', $this->factory);
    $this->factory->setReturnValue('open', $template);
  }


  function tearDown() {
    unset($this->factory);
  }


  function test_should_have_no_attributes() {
    $template = $this->factory->open('');
    $this->assertEqual(0, sizeof($template->get_attributes()));
  }


  function test_should_not_be_empty_after_setting_an_attribute() {
    $template = $this->factory->open('');
    $template->set_attribute('foo', 'bar');
    $this->assertNotEqual(0, sizeof($template->get_attributes()));
  }


  function test_should_be_empty_after_clear() {

    $template = $this->factory->open('foo');

    $this->assertEqual(0, sizeof($template->get_attributes()));

    $template->clear_attributes();
    $this->assertEqual(0, sizeof($template->get_attributes()));
  }
}


class ATemplate extends UnitTestCase {


  var $factory;


  function setUp() {
    $this->factory = new MockFlexi_TemplateFactory(TEST_DIR . '/templates/template_tests');
    $template = new MockTemplate();
    $template->__construct('foo', $this->factory);
    $this->factory->setReturnValue('open', $template);
  }


  function tearDown() {
    unset($this->factory);
  }


  function test_should_return_an_previously_set_attribute() {
    $template = $this->factory->open('foo');
    $template->set_attribute('whom', 'bar');
    $this->assertEqual('bar', $template->get_attribute('whom'));
  }


  function test_should_return_previously_set_attributes() {

    $template = $this->factory->open('foo');
    $template->set_attributes(array('whom' => 'bar', 'foo' => 'baz'));

    $attributes = $template->get_attributes();
    $this->assertIsA($attributes, 'array');
    $this->assertEqual(2, sizeof($attributes));
    $this->assertEqual('bar', $attributes['whom']);
    $this->assertEqual('baz', $attributes['foo']);
  }


  function test_should_merge_attributes_with_set_attributes() {

    $template = $this->factory->open('foo');
    $template->set_attributes(array('a' => 1, 'b' => 2));

    $this->assertEqual(2, sizeof($template->get_attributes()));
    $this->assertEqual(1, $template->get_attribute('a'));
    $this->assertEqual(2, $template->get_attribute('b'));

    $template->set_attributes(array('b' => 8, 'c' => 9));

    $this->assertEqual(3, sizeof($template->get_attributes()));
    $this->assertEqual(1, $template->get_attribute('a'));
    $this->assertEqual(8, $template->get_attribute('b'));
    $this->assertEqual(9, $template->get_attribute('c'));
  }


  function test_should_be_empty_after_clear() {

    $template = $this->factory->open('foo');

    $template->set_attributes(array('a' => 1, 'b' => 2));
    $this->assertNotEqual(0, sizeof($template->get_attributes()));

    $template->clear_attributes();
    $this->assertEqual(0, sizeof($template->get_attributes()));
  }
}



class MagicMethodsTemplate extends UnitTestCase {


  var $factory;


  function setUp() {
    $this->factory = new MockFlexi_TemplateFactory(TEST_DIR . '/templates/template_tests');
    $this->template = new MockTemplate();
    $this->template->__construct('foo', $this->factory);
  }


  function tearDown() {
    unset($this->factory);
    unset($this->template);
  }


  function test_should_set_an_attribute_using_the_magic_methods() {
    $this->template->foo = 'bar';
    $this->assertEqual('bar', $this->template->get_attribute('foo'));
  }


  function test_should_not_set_a_protected_member_field_as_an_attribute() {
    $this->template->_layout = 'bar';
    $this->assertNull($this->template->get_attribute('_layout'));
    $this->assertEqual('bar', $this->template->_layout);
  }

  function test_should_overwrite_an_attribute() {
    $this->template->set_attribute('foo', 'bar');
    $this->template->foo = 'baz';
    $this->assertEqual('baz', $this->template->get_attribute('foo'));
  }

  function test_should_return_an_existing_attribute_using_the_magic_methods() {
    $this->template->set_attribute('foo', 'bar');
    $this->assertEqual('bar', $this->template->foo);
  }


  function test_should_return_null_for_a_non_existing_attribute_using_the_magic_methods() {
    $this->assertNull($this->template->foo);
  }


  function test_should_unset_an_attribute_using_the_magic_methods() {
    $this->template->foo = 'bar';
    unset($this->template->foo);
    $this->assertNull($this->template->foo);
  }


  function test_should_return_null_on_unsetting_a_non_attribute() {
    unset($this->template->foo);
    $this->assertNull($this->template->foo);
  }


  function test_should_return_true_on_isset_for_an_attribute() {
    $this->template->foo = 'bar';
    $this->assertTrue(isset($this->template->foo));
  }


  function test_should_return_false_on_isset_for_a_non_existing_attribute() {
    $this->assertFalse(isset($this->template->foo));
  }
}

