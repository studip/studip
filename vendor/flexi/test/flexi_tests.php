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

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/mock_objects.php';
require_once 'simpletest/autorun.php';

require_once 'varstream.php';

/**
 * Setting up tests for flexi.
 *
 * @package     flexi
 * @subpackage  test
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */

class Flexi_Tests {

  /**
   * Calling this function initializes everything necessary to test flexi.
   *
   * @return void
   */
  function setup() {
    static $once;

    if (!$once) {

      # define TEST_DIR
      define('TEST_DIR', dirname(__FILE__));

      # load required files
      require_once TEST_DIR . '/../lib/flexi.php';

      $once = TRUE;
    }
  }
}
