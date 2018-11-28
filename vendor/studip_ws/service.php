<?php

/*
 * service.php - Abstract super class of all Stud.IP webservices.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * This class is the abstract superclass of all available Stud.IP webservices.
 * You have to extend it when implementing your own webservice.
 *
 * @package     studip
 * @subpackage  ws
 *
 * @abstract
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: service.php 3888 2006-09-06 13:27:19Z mlunzena $
 */

class Studip_Ws_Service {
  
  
  /**
   * <FieldDescription>
   *
   * @access private
   * @var array
   */
  var $api_methods = array();


  /**
   * This method is called before every service method.
   *
   * @param string  the function's name.
   * @param array   an array of arguments that will be delivered to the function.
   *
   * @return mixed  if this method returns a "Studip_Ws_Fault" or "FALSE",
   *                further processing will be aborted and a "Studip_Ws_Fault"
   *                delivered.
   */
  function before_filter(&$name, &$args) {
  }


  /**
   * This method is called after every service method. You may modify the
   * result of that call. This way you can easily implement filters.
   *
   * @param string  the function's name.
   * @param array   an array of arguments that were delivered to the function.
   * @param mixed   the result of the last service method call.
   *
   * @return void
   */
  function after_filter(&$name, &$args, &$result) {
  }

  
  /**
   * <MethodDescription>
   *
   * @access protected
   *
   * @param string the methods name
   * @param array  <description>
   * @param mixed  <description>
   * @param string the description of the method
   *
   * @return void
   */
  function add_api_method($name, $expects = NULL, $returns = NULL,
                          $description = NULL) {

    # check $name
   if (!method_exists($this, $name . '_action'))
     trigger_error(sprintf('No such method exists: %s.', $name), E_USER_ERROR);

    if (isset($this->api_methods[$name])) {
      trigger_error(sprintf('Method %s already added.', $name), E_USER_ERROR);
      return NULL;
    }
    
    return $this->api_methods[$name] =
      new Studip_Ws_Method($this, $name, $expects, $returns, $description);
  }
  
  
  /**
   * Returns the defined API methods of this service.
   *
   * @return array the API methods
   */
  function &get_api_methods() {
    return $this->api_methods;
  }
}
