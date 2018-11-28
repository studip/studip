<?php

/*
 * fault.php - Abstraction of service's faults
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * Abstraction of service's faults
 *
 * @package     studip
 * @subpackage  ws
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: fault.php 3888 2006-09-06 13:27:19Z mlunzena $
 */

class Studip_Ws_Fault {


  /**
   * The fault's message.
   *
   * @access private
   * @var string
   */
  var $message;
  

  /**
   * Constructor.
   *
   * @param string the fault's message
   *
   * @return void
   */
  function __construct($message) {
    $this->message = (string) $message;
  }


  /**
   * Returns the faults message.
   *
   * @return string <description>
   */
  function get_message() {
  	return $this->message;
  }
}
