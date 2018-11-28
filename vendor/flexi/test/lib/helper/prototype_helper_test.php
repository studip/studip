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
require_once dirname(__FILE__) . '/../../../lib/helper/prototype_helper.php';

/**
 * Testcase for JsHelper.
 *
 * @package    flexi
 * @subpackage test
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: prototype_helper_test.php 4194 2006-10-24 14:52:31Z mlunzena $
 */

class PrototypeHelperTestCase extends UnitTestCase {

  function setUp() {
  }

  function tearDown() {
  }

  function test_link_to_remote() {

    $foo = PrototypeHelper::link_to_remote('Delete this post',
      array('update' => 'posts',
            'url' => 'http://destroy?id=1'));
    $exp = '<a href="#" onclick="new Ajax.Updater(\'posts\', \'http://destroy?id=1\', {asynchronous:true, evalScripts:false}); return false;">Delete this post</a>';
    $this->assertEqual($exp, $foo);


    $foo = PrototypeHelper::link_to_remote('Delete this post',
      array('update' => array('success' => 'posts',
                              'failure' => 'error'),
            'url' => 'http://destroy?id=1'));
    $exp = '<a href="#" onclick="new Ajax.Updater({success:\'posts\',failure:\'error\'}, \'http://destroy?id=1\', {asynchronous:true, evalScripts:false}); return false;">Delete this post</a>';
    $this->assertEqual($exp, $foo);


    $foo = PrototypeHelper::link_to_remote('Delete this post',
      array('url' => 'http://destroy?id=1',
            404 => "alert('Not found...?')",
            'failure' => "alert('HTTPError!')"));
    $exp = '<a href="#" onclick="new Ajax.Request(\'http://destroy?id=1\', {asynchronous:true, evalScripts:false, on404:function(request, json){alert(\'Not found...?\')}, onFailure:function(request, json){alert(\'HTTPError!\')}}); return false;">Delete this post</a>';
    $this->assertEqual($exp, $foo);
  }

  function test_periodically_call_remote() {
    $foo = PrototypeHelper::periodically_call_remote(
      array('url' => 'http://clock',
            'frequency' => 2,
            'update' => 'clock'));
    $exp = <<<EXPECTED
<script type="text/javascript">
//<![CDATA[
new PeriodicalExecuter(function() {new Ajax.Updater('clock', 'http://clock', {asynchronous:true, evalScripts:false})}, 2)
//]]>
</script>
EXPECTED;
    $this->assertEqual($exp, $foo);
  }

  function test_form_remote_tag() {

    $foo = PrototypeHelper::form_remote_tag(array(
      'url'      => 'http://tag_add',
      'update'   => 'question_tags',
      'loading'  => "Element.show('indicator'); tag.value = '';",
      'complete' => "Element.hide('indicator');"));
    $exp = <<<EXPECTED
<form onsubmit="new Ajax.Updater('question_tags', 'http://tag_add', {asynchronous:true, evalScripts:false, onComplete:function(request, json){Element.hide('indicator');}, onLoading:function(request, json){Element.show('indicator'); tag.value = '';}, parameters:Form.serialize(this)}); return false;" action="http://tag_add" method="post">
EXPECTED;
    $this->assertEqual($exp, $foo);
  }

  function test_submit_to_remote() {
    $foo = PrototypeHelper::submit_to_remote('name', 'value',
      array(
        'url'      => 'http://tag_add',
        'update'   => 'question_tags',
        'loading'  => "Element.show('indicator'); tag.value = '';",
        'complete' => "Element.hide('indicator');"));
    $exp = <<<EXPECTED
<input type="button" onclick="new Ajax.Updater('question_tags', 'http://tag_add', {asynchronous:true, evalScripts:false, onComplete:function(request, json){Element.hide('indicator');}, onLoading:function(request, json){Element.show('indicator'); tag.value = '';}, parameters:Form.serialize(this.form)}); return false;" name="name" value="value" />
EXPECTED;
    $this->assertEqual($exp, $foo);
  }

  function test_update_element_function() {
    $foo = PrototypeHelper::update_element_function('element_id',
      array('position' => 'bottom', 'content' => "<p>New product!</p>"));
    $exp = "new Insertion.Bottom('element_id','<p>New product!<\/p>');\n";
    $this->assertEqual($exp, $foo);
  }

  function test_evaluate_remote_response() {
    $foo = PrototypeHelper::evaluate_remote_response();
    $exp = 'eval(request.responseText)';
    $this->assertEqual($exp, $foo);
  }

  function test_remote_function() {
    $foo = PrototypeHelper::remote_function(array('update' => 'options',
                                                  'url' => 'http://update'));
    $exp = <<<EXPECTED
new Ajax.Updater('options', 'http://update', {asynchronous:true, evalScripts:false})
EXPECTED;
    $this->assertEqual($exp, $foo);
  }

  function test_observe_field() {
    $foo = PrototypeHelper::observe_field('element_id',
      array('url'       => 'http://update',
            'frequency' => 0,
            'update'    => 'update_id'));
    $exp = <<<EXPECTED
<script type="text/javascript">
//<![CDATA[
new Form.Element.EventObserver("element_id", 0, function(element, value) {new Ajax.Updater('update_id', 'http://update', {asynchronous:true, evalScripts:false, parameters:value})});
//]]>
</script>
EXPECTED;
    $this->assertEqual($exp, $foo);
  }

  function test_observe_form() {
    $foo = PrototypeHelper::observe_form('form_id',
      array('url'       => 'http://update',
            'frequency' => 0,
            'update'    => 'update_id'));
    $exp = <<<EXPECTED
<script type="text/javascript">
//<![CDATA[
new Form.EventObserver("form_id", 0, function(element, value) {new Ajax.Updater('update_id', 'http://update', {asynchronous:true, evalScripts:false, parameters:value})});
//]]>
</script>
EXPECTED;
    $this->assertEqual($exp, $foo);
  }

  function test_jsgen() {
    $page = new Flexi_JavascriptGenerator();
    $page->insert_html('bottom', 'list', '<li>Last item</li>');
    $page->replace_html('person-45', 'Deleted.');
    $page->replace('person-45', 'Deleted.');
    $page->remove('id-1', 'id-2', 'id-3');
    $page->show('id-1', 'id-2', 'id-3');
    $page->hide('id-1', 'id-2', 'id-3');
    $page->toggle('id-1', 'id-2', 'id-3');
    $page->alert('Some "Message"!\/');
    $page->redirect_to('http://www.google.com');
    $page->delay('alert("Hallo Welt")', 23);
    $page->visual_effect('highlight', 'id-42');
    # TODO (mlunzena) no asserts
    # var_dump($page->to_s());
  }
}
