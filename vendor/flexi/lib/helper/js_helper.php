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
 * Provides functionality for working with JavaScript in your views.
 *
 * @package    flexi
 * @subpackage helper
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @author    David Heinemeier Hansson
 * @copyright (c) Authors
 * @version   $Id: js_helper.php 3437 2006-05-27 11:38:58Z mlunzena $
 */

class JsHelper {

  /**
   * Returns a link that'll trigger a javascript function using the
   * onclick handler and return false after the fact.
   *
   * Example:
   *   JsHelper::link_to_function('Greeting', "alert('Hello world!')");
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function link_to_function($name, $function, $html = array()) {
    $html['href'] = isset($html['href']) ? $html['href'] : '#';
    $html['onclick'] = $function.'; return false;';
    return TagHelper::content_tag('a', $name, $html);
  }

  /**
   * Returns a link that'll trigger a JavaScript function using the onclick
   * handler.
   *
   * Examples:
   *   JsHelper::button_to_function("Greeting", "alert('Hello world!')");
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function button_to_function($name, $function, $html_options = array()) {

    $html_options['type'] = 'button';
    $html_options['value'] = $name;
    $html_options['onclick'] = sprintf('%s%s;',
      isset($html_options['onclick']) ? $html_options['onclick'] . '; ' : '',
      $function);
    return TagHelper::tag('input', $html_options);
  }

  /**
   * Escape carrier returns and single and double quotes for Javascript
   * segments.
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function escape_javascript($javascript = '') {
    $pattern = array();    $replace = array();
    $pattern[] = '/\\\\/'; $replace[] = '\\\\\\';
    $pattern[] = '/<\//';  $replace[] = '<\\/';
    $pattern[] = "/\r\n/"; $replace[] = '\n';
    $pattern[] = "/\n/";   $replace[] = '\n';
    $pattern[] = "/\r/";   $replace[] = '\n';
    $pattern[] = '/"/';    $replace[] = '\\"';
    $pattern[] = "/'/";    $replace[] = "\\'";

    $javascript = preg_replace($pattern, $replace, $javascript);
    return $javascript;
  }

  /**
   * Returns a JavaScript tag with the '$content' inside.
   * Example:
   *   JsHelper::javascript_tag("alert('All is good')");
   *   => <script type="text/javascript">alert('All is good')</script>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function javascript_tag($content) {
    return TagHelper::content_tag('script',
                                  JsHelper::js_cdata_section($content),
                                  array('type' => 'text/javascript'));
  }

  /**
   * @ignore
   */
  function js_cdata_section($content) {
    return "\n//".TagHelper::cdata_section("\n$content\n//")."\n";
  }

  /**
   * @ignore
   */
  function options_for_javascript($opt) {
    $opts = array();
    foreach ($opt as $key => $value)
      $opts[] = "$key:$value";
    sort($opts);

    return '{'.join(', ', $opts).'}';
  }
}
