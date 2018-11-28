<?php

/*
 * class.soap_server_delegate.php - Delegate for the DelegatingSoapServer.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * SoapServerDelegate is the abstract super class for all delegates of the
 * DelegatingSoapServer. A sub class has to implement the functions:
 * - boolean responds_to(string);
 * - mixed invoke(string, array);
 *
 * @abstract
 *
 * @package   nusoap
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: class.soap_server_delegate.php 3823 2006-08-29 10:52:08Z mlunzena $
 */

class SoapServerDelegate {


  /**
   * This method is called to verify the existence of a soap-mapped function.
   *
   * @param string the soap-mapped function's name
   *
   * @return boolean returns TRUE, if the delegate can invoke the given
   *                 function, FALSE otherwise
   */
  function responds_to($function) {
    return FALSE;
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
  function invoke($function, $argument_array) {
    return new soap_fault('Server', '', 'SoapServerDelegate is abstract.');
  }
  
  
  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return void
   */
  function register_operations(&$server) {
  	return;
  }
}
