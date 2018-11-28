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
require_once dirname(__FILE__) . '/../../../lib/helper/scriptaculous_helper.php';

/**
 * Testcase for ScriptaculousHelper.
 *
 * @package    flexi
 * @subpackage test
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */

class ScriptaculousHelperTestCase extends UnitTestCase {

  function setUp() {
  }

  function tearDown() {
  }

  function test_visual_effect() {
    $result = ScriptaculousHelper::visual_effect('toggle_blind', 'id');
    $expected = 'new Effect.toggle(\'id\', \'blind\', {})';
    $this->assertEqual($result, $expected);
  }

  function test_sortable_element() {
    $result = ScriptaculousHelper::sortable_element('list', array(
      'url'         => '/',
      'containment' => array('left', 'right'),
      'only'        => 'middle',
    ));
    $expected = <<<SCRIPT
<script type="text/javascript">
//<![CDATA[
Sortable.create('list', {containment:['left','right'], onUpdate:function(){new Ajax.Request('/', {asynchronous:true, evalScripts:false, parameters:Sortable.serialize('list')})}, only:'middle'})
//]]>
</script>
SCRIPT;
    $this->assertEqual($result, $expected);
  }
}
