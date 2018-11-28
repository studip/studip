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

require_once dirname(__FILE__) . '/../vendor/mustache.php/Mustache.php';

/**
 * A template engine that uses PHP to render templates.
 *
 * @package   flexi
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id$
 */

class Flexi_MustacheTemplate extends Flexi_Template {

  /**
   * Parse, render and return the presentation.
   *
   * @return string A string representing the rendered presentation.
   */
  function _render() {
    ${0} = new MyMustache($this);
    $content_for_layout = ${0}->render();

    // include layout, parse it and get output
    if (isset($this->_layout)) {
      $defined = get_defined_vars();
      unset($defined['this'], $defined['0']);
      $content_for_layout = $this->_layout->render($defined);
    }

    return $content_for_layout;
  }

  function getPartial($tag_name) {
      $file = dirname($this->_template) . '/' . $tag_name . '.mustache';
      var_dump($file);
      if (file_exists($file)) {
          return file_get_contents($file);
      }
      return '';
  }
}

class MyMustache extends Mustache {

    public function __construct($flexi) {
        parent::__construct(file_get_contents($flexi->_template), $flexi->_attributes);
        $this->_flexi = $flexi;
    }

    protected function _getPartial($tag_name) {

        if ($partial = $this->_flexi->getPartial($tag_name)) {
            return $partial;
        }

        if ($this->_throwsException(MustacheException::UNKNOWN_PARTIAL)) {
            throw new MustacheException('Unknown partial: ' . $tag_name, MustacheException::UNKNOWN_PARTIAL);
        } else {
            return '';
        }
    }
}
