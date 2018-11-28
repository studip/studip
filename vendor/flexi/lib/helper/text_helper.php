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

/**
 * TextHelper.
 *
 *
 * @package    flexi
 * @subpackage helper
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id$
 */

class TextHelper {

  /**
   * Holds the internal cycles.
   *
   * @ignore
   */
  private static $cycles = array();


  /**
   * Returns a camelized string from a lower case and underscored string by
   * replacing slash with underscore and upper-casing each letter preceded
   * by an underscore.
   *
   * @param string String to camelize.
   *
   * @return string Camelized string.
   */
  function camelize($word) {
    return str_replace(' ', '',
                       ucwords(str_replace(array('_', '/'),
                                           array(' ', ' '), $word)));
  }


  /**
   * Creates a Cycle object whose +__toString method cycles through elements of
   * an array every time it is called. This can be used for example, to
   * alternate classes for table rows:
   *
   *   <? foreach ($items as $item) : ?>
   *     <tr class="<?= TextHelper::cycle('odd', 'even') ?>">
   *       <td><?= item ?></td>
   *     </tr>
   *   <? endforeach ?>
   *
   * You can use named cycles to allow nesting in loops.  Passing a single array
   * as the only parameter with a <tt>name</tt> key will create a named cycle.
   * You can manually reset a cycle by calling reset_cycle and passing the
   * name of the cycle.
   *
   *   <? foreach($items as $item) : ?>
   *     <tr class="<?= TextHelper::cycle(array("even", "odd", "name" => "row_class")) ?>">
   *       <td>
   *         <? foreach ($item->values as $value) : ?>
   *           <span style="color:<?= TextHelper::cycle(array("red", "green", "blue", "name" => "colors")) : ?>">
   *             <?= $value ?>
   *           </span>
   *         <? endforeach ?>
   *         <? TextHelper::reset_cycle("colors") ?>
   *       </td>
   *    </tr>
   *  <? endforeach ?>
   */
  static function cycle($first_value) {

    $values = func_get_args();


    if (sizeof($values) == 1 && is_array($values[0])) {
      $values = $values[0];
      $name = isset($values['name']) ? $values['name'] : 'default';
      unset($values['name']);
    }
    else {
      $name = 'default';
    }

    $cycle = self::get_cycle($name);
    if (is_null($cycle) || $cycle->values !== $values) {
      $cycle = self::set_cycle($name, new TextHelperCycle($values));
    }

    return (string) $cycle;
  }


  /**
   * Resets a cycle so that it starts from the first element the next time
   * it is called. Pass in +name+ to reset a named cycle.
   *
   * @param  string   an optional name of a cycle
   *
   * @return void
   */
  function reset_cycle($name = 'default') {
    $cycle = self::get_cycle($name);
    if (isset($cycle)) {
      $cycle->reset();
    }
  }


  /**
   * @ignore
   */
  private static function get_cycle($name) {
    return isset(self::$cycles[$name]) ? self::$cycles[$name] : NULL;
  }

  /**
   * @ignore
   */
  private static function set_cycle($name, $cycle) {
    return self::$cycles[$name] = $cycle;
  }
}


/**
 * This class holds an array of string and cycles through them.
 *
 * @package     flexi
 * @subpackage  helper
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id$
 */

class TextHelperCycle {


  public $values;


  function __construct($values) {
    $this->values = (array) $values;
  }


  function cycle() {
    $result = current($this->values);
    if (next($this->values) === FALSE)
      $this->reset();
    return $result;
  }


  function reset() {
    reset($this->values);
  }


  function __toString() {
    return $this->cycle();
  }
}

