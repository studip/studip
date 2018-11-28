<?php

/*
 * dispatcher.php - <short-description>
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * <ClassDescription>
 *
 * @package     studip
 * @subpackage  ws
 *
 * @abstract
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: dispatcher.php 3888 2006-09-06 13:27:19Z mlunzena $
 */

class Studip_Ws_Dispatcher {

	
  /**
   * <FieldDescription>
   *
   * @access private
   * @var array
   */
  var $api_methods = array();
  

  /**
   * Constructor. Give an unlimited number of services' class names as
   * arguments.
   *
   * @param mixed $services,... an unlimited number or an array of class names
   *                            of services to include
   *
   * @return void
   */
  function __construct($services = array() /*, ... */) {

    if (!is_array($services))
      $services = func_get_args();

    foreach ($services as $service_name) {
      $this->add_service($service_name);
    }
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return bool <description>
   */
  function add_service($service_name) {

    if (!is_string($service_name)) {
      trigger_error('Arguments must be strings.', E_USER_WARNING);        
      return FALSE;
    }
    
    # not a service
    if (!class_exists($service_name) ||
        !$this->is_a_service($service_name)) {
      trigger_error(sprintf('Service "%s" does not exist.', $service_name),
                    E_USER_WARNING);        
      return FALSE;
    }

    $service = new $service_name();

    $api_methods = $service->get_api_methods();

    foreach ($api_methods as $method_name => $method) {
      
      if (isset($this->api_methods[$method_name])) {
        trigger_error(sprintf('Method %s already defined.', $method_name),
                      E_USER_ERROR);
        return FALSE;
      }

      $this->api_methods[$method_name] =& $api_methods[$method_name];
    }
    
    return TRUE;
  }

  /**
   * This method is called to verify the existence of a mapped function.
   *
   * @param string  the function's name
   *
   * @return boolean returns TRUE, if the dispatcher can invoke the given
   *                 function, FALSE otherwise
   */
  function responds_to($function) {
    return isset($this->api_methods[(string)$function]);
  }


  /**
   * This method is responsible to call the given function with the given
   * arguments.
   *
   * @param string the name of the function to invoke
   * @param array an array of arguments
   *
   * @return mixed the return value of the invoked function
   */
  function &invoke($method0, $argument_array) {

    # find service that provides $method
    if (!isset($this->api_methods[$method0]))
      return $this->throw_exception('No service responds to "%s".', $method0);
      
    $service = $this->api_methods[$method0]->service;

    # calling before filter
    $before = $service->before_filter($method0, $argument_array);
    if ($before === FALSE || is_a($before, 'Studip_Ws_Fault')) {
      $msg = $before ? $before->get_message() : 'before_filter activated.';
      $exception = $this->throw_exception($msg);
      return $exception;
    }

    $method = Studip_Ws_Dispatcher::map_function($method0);

    # call actual function
    $result = call_user_func_array(array(&$service, $method), $argument_array);
    
    # calling after filter
    $service->after_filter($method0, $argument_array, $result);

    if (is_a($result, 'Studip_Ws_Fault')) {
      $exception = $this->throw_exception($result->get_message());
      return $exception;
    }

    return $result; 
  }


  /**
   * Replacement for "x instanceof Studip_Ws_Service".
   *
   * @todo Should not this be elsewhere?
   *
   * @access private
   *
   * @param mixed a string or an object to get checked
   *
   * @return bool returns TRUE if the argument was a Studip_Ws_Service
   */
  function is_a_service($class) {
    
    if (!is_string($class)) {
      if (is_object($class)) {
        $class = get_class($class);
      } else {
        trigger_error('Argument has to be a string or an object.', 
                      E_USER_ERROR);
        return FALSE;
      }
    }
      
    if (strcasecmp($class, 'Studip_Ws_Service') === 0)
      return TRUE;

    if ($parent = get_parent_class($class))
      return Studip_Ws_Dispatcher::is_a_service($parent);
    
    return FALSE;
  }


  /**
   * Maps a RPC operation name to it's real world function name.
   *
   * @access private
   *
   * @param string <description>
   *
   * @return string <description>
   */
  function map_function($function) {
    return $function . '_action';
  }
  
  
  /**
   * <MethodDescription>
   *
   * @access private
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function throw_exception($message/*, ...*/) {
    $args = func_get_args();
    trigger_error(vsprintf(array_shift($args), $args), E_USER_ERROR);
    return NULL;
  }
}
