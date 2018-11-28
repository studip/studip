<?php

/*
 * class.delegating_soap_server.php - A SOAP server that delegates invocation.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * DelegatingSoapServer extends the basic soap_server and delegates the
 * actual invocation of php functions and methods to a another object.
 *
 *
 * @package   nusoap
 *
 * @author    Marcus Lunzenauer <mlunzena@uos.de>
 * @author    Dietrich Ayala <dietrich@ganx4.com>
 * @copyright (c) Authors
 * @version   $Id: class.delegating_soap_server.php 3823 2006-08-29 10:52:08Z mlunzena $
 */

class DelegatingSoapServer extends soap_server {

  /**
   * The delegate which invokes soap functions.
   *
   * @access private
   * @var mixed
   */
  var $delegate;


  /**
   * Constructor.
   * The optional parameter is a path to a WSDL file that you'd like to bind the
   * server instance to.
   *
   * @param mixed file path or URL (string), or wsdl instance (object)
   *
   * @return void
   */
  function __construct(&$delegate, $wsdl = FALSE) {
    parent::__construct($wsdl);
    $this->delegate =& $delegate;
  }


  /**
   * Invokes a PHP function for the requested SOAP method.
   *
   * The following fields are set by this function (when successful):
   * - methodreturn
   *
   * Note that the PHP function that is called may also set the following
   * fields to affect the response sent to the client:
   * - responseHeaders
   * - outgoing_headers
   *
   * @return void
   */
  function invoke_method() {

    $this->debug(
      sprintf('in invoke_method, methodname=%s methodURI=%s SOAPAction=%s',
              $this->methodname, $this->methodURI, $this->SOAPAction));

    if ($this->wsdl) {

      if ($this->opData = $this->wsdl->getOperationData($this->methodname)) {
        $this->debug('in invoke_method, found WSDL operation=' .
                     $this->methodname);
        $this->appendDebug('opData=' . $this->varDump($this->opData));
      }

      else if ($this->opData = $this->wsdl->getOperationDataForSoapAction($this->SOAPAction)) {

        # Note: hopefully this case will only be used for doc/lit,
        # since rpc services should have wrapper element
        $this->debug('in invoke_method, found WSDL soapAction=' .
                     $this->SOAPAction . ' for operation=' .
                     $this->opData['name']);
        $this->appendDebug('opData=' . $this->varDump($this->opData));
        $this->methodname = $this->opData['name'];
      }

      else {
        $this->debug('in invoke_method, no WSDL for operation=' .
                     $this->methodname);
        $this->fault('Client', "Operation '" . $this->methodname .
                     "' is not defined in the WSDL for this service");
        return;
      }
    }

    else {
      $this->debug('in invoke_method, no WSDL to validate method');
    }

    # does method exist?
    if (!$this->delegate->responds_to($this->methodname)) {
      $this->debug("in invoke_method, function '$this->methodname' not found!");
      $this->result = 'fault: method not found';
      $this->fault('Client',
                   "method '$this->methodname' not defined in service");
      return;
    }

    # evaluate message, getting back parameters
    # verify that request parameters match the method's signature
    if (!$this->verify_method($this->methodname, $this->methodparams)){
      # debug
      $this->debug('ERROR: request not verified against method signature');
      $this->result = 'fault: request failed validation against method '.
                      'signature';
      # return fault
      $this->fault('Client',
                   "Operation '$this->methodname' not defined in service.");
      return;
    }

    # if there are parameters to pass
    $this->debug('in invoke_method, params:');
    $this->appendDebug($this->varDump($this->methodparams));
    $this->debug("in invoke_method, calling '$this->methodname'");

    $this->methodreturn = $this->delegate->invoke($this->methodname,
                                                  $this->methodparams);

    $this->debug("in invoke_method, received ".$this->varDump($this->methodreturn)." of type " .
                 gettype($this->methodreturn));
  }
}
