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
 * TagHelper defines some base helpers to construct html tags.
 * This is poor man’s Builder for the rare cases where you need to
 * programmatically make tags but can’t use Builder.
 *
 * @package    flexi
 * @subpackage helper
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @author    Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author    David Heinemeier Hansson
 * @copyright (c) Authors
 * @version   $Id: tag_helper.php 3437 2006-05-27 11:38:58Z mlunzena $
 */

class TagHelper {

  /**
   * Constructs an html tag.
   *
   * @param  $name    string  tag name
   * @param  $options array   tag options
   * @param  $open    boolean true to leave tag open
   *
   * @return string
   */
  function tag($name, $options = array(), $open = false) {
    if (!$name) return '';
    return '<'.$name.TagHelper::_tag_options($options).($open ? '>' : ' />');
  }

  /**
   * Helper function for content tags.
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function content_tag($name, $content = '', $options = array()) {
    if (!$name) return '';
    return '<'.$name.TagHelper::_tag_options($options).'>'.$content.'</'.$name.'>';
  }

  /**
   * Helper function for CDATA sections.
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function cdata_section($content) {
    return '<![CDATA['.$content.']]>';
  }

  /**
   * @ignore
   */
  function _tag_options($options = array()) {
    $options = TagHelper::_parse_attributes($options);
    $html = '';
    foreach ($options as $key => $value)
      $html .= ' '.$key.'="'.$value.'"';
    return $html;
  }

  /**
   * @ignore
   */
  function _parse_attributes($string) {
    return is_array($string) ? $string : TagHelper::string_to_array($string);
  }

  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return array <description>
   */
  function string_to_array($string) {
    preg_match_all('/
      \s*(\w+)              # key                               \\1
      \s*=\s*               # =
      (\'|")?               # values may be included in \' or " \\2
      (.*?)                 # value                             \\3
      (?(2) \\2)            # matching \' or " if needed        \\4
      \s*(?:
        (?=\w+\s*=) | \s*$  # followed by another key= or the end of the string
      )
    /x', $string, $matches, PREG_SET_ORDER);

    $attributes = array();
    foreach ($matches as $val)
      $attributes[$val[1]] = $val[3];

    return $attributes;
  }
}
