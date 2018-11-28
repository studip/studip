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
require_once dirname(__FILE__) . '/../../../lib/helper/js_helper.php';

/**
 * Testcase for JsHelper.
 *
 * @package    flexi
 * @subpackage test
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: js_helper_test.php 4194 2006-10-24 14:52:31Z mlunzena $
 */

class JsHelperTestCase extends UnitTestCase {

  function setUp() {
  }

  function tearDown() {
  }


  function test_link_to_function() {
    $foo = JsHelper::link_to_function('Greeting', "alert('Hello world!')",
                                      array('title' => 'hello'));
    $exp = '<a title="hello" href="#" onclick="alert(\'Hello world!\'); '.
           'return false;">Greeting</a>';
    $this->assertEqual($exp, $foo);
  }

  function test_button_to_function() {
    $foo = JsHelper::button_to_function("Greeting", "alert('Hello world!')");
    $exp = '<input type="button" value="Greeting" '.
           'onclick="alert(\'Hello world!\');" />';
    $this->assertEqual($exp, $foo);

    $foo = JsHelper::button_to_function("Greeting", "alert('Hello world!')",
                                        array('onclick' => 'alert(\'One\')'));
    $exp = '<input onclick="alert(\'One\'); alert(\'Hello world!\');" '.
           'type="button" value="Greeting" />';
    $this->assertEqual($exp, $foo);
  }

  function test_escape_javascript() {
    $strings = array(
    "\r" => '\n',
    "\n" => '\n',
    '\\' => '\\\\',
    '</' => '<\\/',
    '"'  => '\\"',
    "'"  => "\\'"
    );
    foreach ($strings as $string => $expect) {
      $this->assertEqual(JsHelper::escape_javascript($string), $expect);
    }
  }

  function test_javascript_tag() {
    $foo = JsHelper::javascript_tag("alert('All is good')");
    $exp = <<<EXPECTED
<script type="text/javascript">
//<![CDATA[
alert('All is good')
//]]>
</script>
EXPECTED;
    $this->assertEqual($exp, $foo);
  }
}
