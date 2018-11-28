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
 * Abstract template class representing the presentation layer of an action.
 * Output can be customized by supplying attributes, which a template can
 * manipulate and display.
 *
 * @package   flexi
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id$
 */

abstract class Flexi_Template {

  /**
   * @ignore
   */
  public $_attributes, $_factory, $_options, $_layout, $_template;


  /**
   * Constructor
   *
   * @param string                 the path of the template.
   * @param Flexi_TemplateFactory  the factory creating this template
   * @param array                  optional array of options
   *
   * @return void
   */
  function __construct($template, &$factory, $options = array()) {

    # set template
    $this->_template = $template;

    # set factory
    $this->_factory = $factory;

    # set options
    $this->_options = $options;

    # init attributes
    $this->clear_attributes();

    # set layout
    $this->set_layout(NULL);
  }


  /**
   * __set() is a magic method run when writing data to inaccessible members.
   * In this class it is used to set attributes for the template in a
   * comfortable way.
   *
   * @see http://php.net/__set
   *
   * @param  string     the name of the member field
   * @param  mixed      the value for the member field
   *
   * @return void
   */
  function __set($name, $value) {
    $this->set_attribute($name, $value);
  }


  /**
   * __get() is a magic method utilized for reading data from inaccessible
   * members.
   * In this class it is used to get attributes for the template in a
   * comfortable way.
   *
   * @see http://php.net/__set
   *
   * @param  string     the name of the member field
   *
   * @return mixed      the value for the member field
   */
  function __get($name) {
    return $this->get_attribute($name);
  }


  /**
   * __isset() is a magic method triggered by calling isset() or empty() on
   * inaccessible members.
   * In this class it is used to check for attributes for the template in a
   * comfortable way.
   *
   * @see http://php.net/__set
   *
   * @param  string     the name of the member field
   *
   * @return bool       TRUE if that attribute exists, FALSE otherwise
   */
  function __isset($name) {
    return isset($this->_attributes[$name]);
  }


  /**
   * __unset() is a magic method invoked when unset() is used on inaccessible
   * members.
   * In this class it is used to check for attributes for the template in a
   * comfortable way.
   *
   * @see http://php.net/__set
   *
   * @param  string     the name of the member field
   *
   * @return void
   */
  function __unset($name) {
    $this->clear_attribute($name);
  }


  /**
   * Parse, render and return the presentation.
   *
   * @param array  An optional associative array of attributes and their
   *               associated values.
   * @param string A name of a layout template.
   *
   * @return string A string representing the rendered presentation.
   */
  function render($attributes = null, $layout = null) {

    if (isset($layout)) {
      $this->set_layout($layout);
    }

    # merge attributes
    $this->set_attributes($attributes);

    return $this->_render();
  }


  /**
   * Parse, render and return the presentation.
   *
   * @return string A string representing the rendered presentation.
   */
  abstract function _render();


  /**
   * Returns the value of an attribute.
   *
   * @param string An attribute name.
   * @param mixed  An attribute value.
   *
   * @return mixed  An attribute value.
   */
  function get_attribute($name) {
    return isset($this->_attributes[$name]) ? $this->_attributes[$name] : NULL;
  }


  /**
   * Set an array of attributes.
   *
   * @return array An associative array of attributes and their associated
   *               values.
   */
  function get_attributes() {
    return $this->_attributes;
  }


  /**
   * Set an attribute.
   *
   * @param string An attribute name.
   * @param mixed  An attribute value.
   *
   * @return void
   */
  function set_attribute($name, $value) {
    $this->_attributes[$name] = $value;
  }


  /**
   * Set an array of attributes.
   *
   * @param array An associative array of attributes and their associated
   *              values.
   *
   * @return void
   */
  function set_attributes($attributes) {
    $this->_attributes = (array)$attributes + (array)$this->_attributes;
  }


  /**
   * Clear all attributes associated with this template.
   *
   * @return void
   */
  function clear_attributes() {
    $this->_attributes = array();
  }


  /**
   * Clear an attribute associated with this template.
   *
   * @param string The name of the attribute to be cleared.
   *
   * @return void
   */
  function clear_attribute($name) {
    unset($this->_attributes[$name]);
  }


  /**
   * Set the template's layout.
   *
   * @param mixed A name of a layout template or a layout template.
   *
   * @return void
   */
  function set_layout($layout) {
    $this->_layout = $this->_factory->open($layout);
  }
}
