<?php

/*
 * xmlrpc_dispatcher.php - <short-description>
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
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: xmlrpc_dispatcher.php 3888 2006-09-06 13:27:19Z mlunzena $
 */

class Studip_Ws_XmlrpcDispatcher extends Studip_Ws_Dispatcher {


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return mixed <description>
   */
  function dispatch($msg = NULL) {

    # ensure correct invocation
    if (is_null($msg) || !is_a($msg, 'xmlrpcmsg'))
      return $this->throw_exception('functions_parameters_type must not be '.
                                    'phpvals.');

    # get decoded parameters
    $len = $msg->getNumParams();
    $argument_array = array();
    for ($i = 0; $i < $len; ++$i)
      $argument_array[] = php_xmlrpc_decode($msg->getParam($i));

    # return result
    return new xmlrpcresp(
      php_xmlrpc_encode($this->invoke($msg->method(), $argument_array)));
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return mixed <description>
   */
  function throw_exception($message/*, ...*/) {
    $args = func_get_args();
    return new xmlrpcresp(0, $GLOBALS['xmlrpcerruser'] + 1,
                          vsprintf(array_shift($args), $args));
  }


  /**
   * Class method that composes the dispatch map from the available methods.
   *
   * @return array This service's dispatch map.
   *
   */
  function get_dispatch_map() {
    $dispatch_map = array();
    foreach ($this->api_methods as $method_name => $method)
      if ($mapped = $this->map_method($method)) {
        $dispatch_map[$method_name] = $mapped;
      }
    return $dispatch_map;
  }


  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return array <description>
   */
  function map_method($method) {

    # TODO validate method
    try {
      $reflection = new ReflectionMethod($method->service, "{$method->name}_action");
      $parameters = $reflection->getParameters();
    } catch (Exception $e) {
      return false;
    }

    ## 1. function
    $function = array(&$this, 'dispatch');

    ## 2. signature
    $signature      = [[]];
    $signature_docs = [['']];

    # return value
    $signature[0][] = $this->translate_type($method->returns);

    # arguments
    foreach ($method->expects as $index => $type) {
        $signature[0][]      = $this->translate_type($type);
        $signature_docs[0][] = isset($parameters[$index]) ? $parameters[$index]->getName() : '';
    }

    ## 3. docstring
    $docstring = $method->description;

    return compact('function', 'signature', 'docstring', 'signature_docs');
  }


  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return mixed <description>
   */
  function translate_type($type0) {
    switch ($type = Studip_Ws_Type::get_type($type0)) {
      case STUDIP_WS_TYPE_INT:
        return PhpXmlRpc\Value::$xmlrpcInt;

      case STUDIP_WS_TYPE_STRING:
        return PhpXmlRpc\Value::$xmlrpcString;

      case STUDIP_WS_TYPE_BASE64:
        return PhpXmlRpc\Value::$xmlrpcBase64;

      case STUDIP_WS_TYPE_BOOL:
        return PhpXmlRpc\Value::$xmlrpcBoolean;

      case STUDIP_WS_TYPE_FLOAT:
        return PhpXmlRpc\Value::$xmlrpcDouble;

      case STUDIP_WS_TYPE_NULL:
        return PhpXmlRpc\Value::$xmlrpcNull;

      case STUDIP_WS_TYPE_ARRAY:
        return PhpXmlRpc\Value::$xmlrpcArray;

      case STUDIP_WS_TYPE_STRUCT:
        return PhpXmlRpc\Value::$xmlrpcStruct;
    }

    trigger_error(sprintf('Type %s could not be found.',
                          var_export($type, TRUE)),
                  E_USER_ERROR);
    return PhpXmlRpc\Value::$xmlrpcString;
  }
}
