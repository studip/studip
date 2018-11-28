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

class PhpTemplateTestCase extends UnitTestCase {

  var $factory;


  function setUp() {
    $this->setUpFS();
    $this->factory = new Flexi_TemplateFactory('var://templates/');
  }


  function tearDown() {
    unset($this->factory);

    stream_wrapper_unregister("var");
  }

  function setUpFS() {
    ArrayFileStream::set_filesystem(array(
      'templates' => array(

        'foo_using_partial.php' =>
          'Hello, <?= $this->render_partial("foos_partial") ?>!',

        'foos_partial.php' =>
          '<h1><?= $whom ?> at <?= $when ?></h1>',

        'foo_with_partial_collection.php' =>
          '[<?= $this->render_partial_collection("item", $items, "spacer") ?>]',

        'item.php' =>
          '"<?= $item ?>"',

        'spacer.php' =>
          ', ',

        'attributes.php' =>
          '<? foreach (get_defined_vars() as $name => $value) : ?>' .
          '<?= $name ?><?= $value ?>'.
          '<? endforeach ?>',

        'foo.php' =>
          'Hello, <?= $whom ?>!',

        'layout.php' =>
          '[<?= $content_for_layout ?>]',
      )));
    stream_wrapper_register("var", "ArrayFileStream") or die("Failed to register protocol");
  }

  function test_render_partial() {
    $template = $this->factory->open('foo_using_partial');
    $template->set_attribute('whom', 'bar');
    $output = $template->render(array('when' => 'now'));
    $spec = "Hello, <h1>bar at now</h1>!";
    $this->assertEqual($output, $spec);
  }


  function test_render_partial_collection() {
    $template = $this->factory->open('foo_with_partial_collection');
    $result = $template->render_partial_collection('item',
                                                   range(1, 3),
                                                   'spacer');
    $this->assertEqual('"1", "2", "3"', $result);
  }


   function test_should_override_attributes_with_those_passed_to_render() {

     $template = $this->factory->open('attributes');
     $template->set_attribute('foo',  'baz');

     $template->render(array('foo' => 'bar'));

     $bar = $template->get_attribute('foo');
     $this->assertEqual($bar, 'bar');

     $out = $template->render();

     $bar = $template->get_attribute('foo');
     $this->assertEqual($bar, 'bar');
   }

   function test_render_without_layout() {
     $foo = $this->factory->open('foo');
     $foo->set_attribute('whom', 'bar');
     $out = $foo->render();
     $this->assertEqual('Hello, bar!', $out);
   }

   function test_render_with_layout() {
     $foo = $this->factory->open('foo');
     $foo->set_attribute('whom', 'bar');
     $foo->set_layout('layout');
     $out = $foo->render();
     $this->assertEqual('[Hello, bar!]', $out);
   }

   function test_render_with_layout_inline() {
     $out = $this->factory->render('foo', array('whom' => 'bar'), 'layout');
     $this->assertEqual('[Hello, bar!]', $out);
   }

   function test_render_with_missing_layout() {
     $foo = $this->factory->open('foo');
     $this->expectException("Flexi_TemplateNotFoundException");
     $foo->set_layout('nosuchlayout');
   }

   function test_render_with_attributes() {
     $foo = $this->factory->open('foo');
     $foo->set_attribute('whom', 'bar');
     $foo->set_layout('layout');
     $foo_out = $foo->render();

     $bar = $this->factory->open('foo');
     $bar_out = $bar->render(array('whom' => 'bar'), 'layout');

     $this->assertEqual($foo_out, $bar_out);
   }
}


class PhpTemplatePartialBugTestCase extends UnitTestCase {
  function setUp() {
    $this->setUpFS();
    $this->factory = new Flexi_TemplateFactory('var://templates/');
  }

  function tearDown() {
    unset($this->factory);

    stream_wrapper_unregister("var");
  }

  function setUpFS() {
    ArrayFileStream::set_filesystem(array(
      'templates' => array(
        'layout.php' =>
          '<? $do_not_echo_this = $this->render_partial_collection("partial", range(1, 5));'.
          'echo $content_for_layout;',
        'partial.php' =>
          'partial',
        'template.php' =>
          'template',
      )));
    stream_wrapper_register("var", "ArrayFileStream") or die("Failed to register protocol");
  }

  function test_partial_bug() {
    $template = $this->factory->open('template');
    $template->set_layout('layout');
    $result = $template->render();
    $this->assertEqual($result, "template");
  }
}
